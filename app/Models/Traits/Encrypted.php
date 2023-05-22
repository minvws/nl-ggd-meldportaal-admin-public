<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use SodiumException;

/**
 * Trait Encrypted
 *
 * @author annejan@noprotocol.nl
 * @package App\Models\Traits
 */
trait Encrypted
{
    /**
     * Decrypt required data, checking for changed crypto keys.
     *
     * @param string $key
     * @return mixed
     * @throws SodiumException|DecryptException|Exception
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (! in_array($key, $this->encrypted)) {
            return $value;
        }

        $decoded = json_decode(base64_decode($value));
        if ($decoded === false) {
            throw new DecryptException('Encoding broken.');
        }

        if ($this->getKeyHash() !== $decoded->hash) {
            throw new DecryptException('Encrypted with different key.');
        }

        $plain = sodium_crypto_secretbox_open(
            sodium_hex2bin($decoded->value),
            sodium_hex2bin($decoded->nonce),
            config('crypto.database_at_rest_key')
        );
        sodium_memzero($value);
        if ($plain === false) {
            throw new DecryptException('Decoding failed.');
        }

        return $plain;
    }

    /**
     * Store given data in base64 encoded json array.
     * {
     *   "nonce": "hex encoded 32 random bytes",
     *   "value": "hex encoded sodium secretbox encrypted data",
     *   "hash": "hex encoded sodium generic hash of used key",
     * }
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     * @throws SodiumException
     * @throws Exception
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encrypted)) {
            $nonce = random_bytes(
                SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
            );
            $payload = json_encode([
                'nonce' => sodium_bin2hex($nonce),
                'value' => sodium_bin2hex(
                    sodium_crypto_secretbox(
                        $value,
                        $nonce,
                        config('crypto.database_at_rest_key'),
                    ),
                ),
                'hash' => $this->getKeyHash(),
            ]);
            sodium_memzero($value);

            if (json_last_error() !== JSON_ERROR_NONE || $payload === false) {
                throw new EncryptException('Could not encrypt the data.');
            }
            $value = base64_encode($payload);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @return string
     * @throws SodiumException
     */
    private function getKeyHash(): string
    {
        return sodium_bin2hex(
            sodium_crypto_generichash(
                config('crypto.database_at_rest_key')
            )
        );
    }
}
