<?php declare(strict_types=1);
namespace Tanker\Identity;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertTrue;

final class IdentityTest extends TestCase
{
    const APP_ID = 'tpoxyNzh0hU9G2i9agMvHyyd+pO6zGCjO9BfhrCLjd4=';
    const APP_SECRET = 'cTMoGGUKhwN47ypq4xAXAtVkNWeyUtMltQnYwJhxWYSvqjPVGmXd2wwa7y17QtPTZhn8bxb015CZC/e4ZI7+MQ==';
    const APP_PUBLIC_KEY = 'r6oz1Rpl3dsMGu8te0LT02YZ/G8W9NeQmQv3uGSO/jE=';
    const USER_ID = 'b_eich';
    const USER_EMAIL = 'brendan.eich@tanker.io';
    const HASHED_USER_ID = 'RDa0eq4XNuj5tV7hdapjOxhmheTh4QBDNpy4Svy9Xok=';
    const PERMANENT_IDENTITY = 'eyJ0cnVzdGNoYWluX2lkIjoidHBveHlOemgwaFU5RzJpOWFnTXZIeXlkK3BPNnpHQ2pPOUJmaHJDTGpkND0iLCJ0YXJnZXQiOiJ1c2VyIiwidmFsdWUiOiJSRGEwZXE0WE51ajV0VjdoZGFwak94aG1oZVRoNFFCRE5weTRTdnk5WG9rPSIsImRlbGVnYXRpb25fc2lnbmF0dXJlIjoiVTlXUW9sQ3ZSeWpUOG9SMlBRbWQxV1hOQ2kwcW1MMTJoTnJ0R2FiWVJFV2lyeTUya1d4MUFnWXprTHhINmdwbzNNaUE5cisremhubW9ZZEVKMCtKQ3c9PSIsImVwaGVtZXJhbF9wdWJsaWNfc2lnbmF0dXJlX2tleSI6IlhoM2kweERUcHIzSFh0QjJRNTE3UUt2M2F6TnpYTExYTWRKRFRTSDRiZDQ9IiwiZXBoZW1lcmFsX3ByaXZhdGVfc2lnbmF0dXJlX2tleSI6ImpFRFQ0d1FDYzFERndvZFhOUEhGQ2xuZFRQbkZ1Rm1YaEJ0K2lzS1U0WnBlSGVMVEVOT212Y2RlMEhaRG5YdEFxL2RyTTNOY3N0Y3gwa05OSWZodDNnPT0iLCJ1c2VyX3NlY3JldCI6IjdGU2YvbjBlNzZRVDNzMERrdmV0UlZWSmhYWkdFak94ajVFV0FGZXh2akk9In0=';
    const PROVISIONAL_IDENTITY = 'eyJ0cnVzdGNoYWluX2lkIjoidHBveHlOemgwaFU5RzJpOWFnTXZIeXlkK3BPNnpHQ2pPOUJmaHJDTGpkND0iLCJ0YXJnZXQiOiJlbWFpbCIsInZhbHVlIjoiYnJlbmRhbi5laWNoQHRhbmtlci5pbyIsInB1YmxpY19lbmNyeXB0aW9uX2tleSI6Ii8yajRkSTNyOFBsdkNOM3VXNEhoQTV3QnRNS09jQUNkMzhLNk4wcSttRlU9IiwicHJpdmF0ZV9lbmNyeXB0aW9uX2tleSI6IjRRQjVUV212Y0JyZ2V5RERMaFVMSU5VNnRicUFPRVE4djlwakRrUGN5YkE9IiwicHVibGljX3NpZ25hdHVyZV9rZXkiOiJXN1FFUUJ1OUZYY1hJcE9ncTYydFB3Qml5RkFicFQxckFydUQwaC9OclRBPSIsInByaXZhdGVfc2lnbmF0dXJlX2tleSI6IlVtbll1dmRUYUxZRzBhK0phRHBZNm9qdzQvMkxsOHpzbXJhbVZDNGZ1cVJidEFSQUc3MFZkeGNpazZDcnJhMC9BR0xJVUJ1bFBXc0N1NFBTSDgydE1BPT0ifQ==';
    const PUBLIC_IDENTITY = 'eyJ0YXJnZXQiOiJ1c2VyIiwidHJ1c3RjaGFpbl9pZCI6InRwb3h5TnpoMGhVOUcyaTlhZ012SHl5ZCtwTzZ6R0NqTzlCZmhyQ0xqZDQ9IiwidmFsdWUiOiJSRGEwZXE0WE51ajV0VjdoZGFwak94aG1oZVRoNFFCRE5weTRTdnk5WG9rPSJ9';
    const PUBLIC_PROVISIONAL_IDENTITY = 'eyJ0cnVzdGNoYWluX2lkIjoidHBveHlOemgwaFU5RzJpOWFnTXZIeXlkK3BPNnpHQ2pPOUJmaHJDTGpkND0iLCJ0YXJnZXQiOiJlbWFpbCIsInZhbHVlIjoiYnJlbmRhbi5laWNoQHRhbmtlci5pbyIsInB1YmxpY19lbmNyeXB0aW9uX2tleSI6Ii8yajRkSTNyOFBsdkNOM3VXNEhoQTV3QnRNS09jQUNkMzhLNk4wcSttRlU9IiwicHVibGljX3NpZ25hdHVyZV9rZXkiOiJXN1FFUUJ1OUZYY1hJcE9ncTYydFB3Qml5RkFicFQxckFydUQwaC9OclRBPSJ9';
    const LOREM_IPSUM_B64 = 'TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQgCg==';

    // https://stackoverflow.com/a/46972615/1401962
    protected function assertException(string $expectClass, callable $callback)
    {
        try {
            $callback();
        } catch (\Throwable $exception) {
            $this->assertInstanceOf($expectClass, $exception, 'An invalid exception was thrown');
            return;
        }

        $this->fail('No exception was thrown');
    }


    public function testParseValidPermanentIdentity(): void
    {
        $identity = Internal\tanker_deserialize_identity(self::PERMANENT_IDENTITY);

        self::assertEquals(self::APP_ID, $identity['trustchain_id']);
        self::assertEquals('user', $identity['target']);
        self::assertEquals(self::HASHED_USER_ID, $identity['value']);
        self::assertEquals('U9WQolCvRyjT8oR2PQmd1WXNCi0qmL12hNrtGabYREWiry52kWx1AgYzkLxH6gpo3MiA9r++zhnmoYdEJ0+JCw==', $identity['delegation_signature']);
        self::assertEquals('Xh3i0xDTpr3HXtB2Q517QKv3azNzXLLXMdJDTSH4bd4=', $identity['ephemeral_public_signature_key']);
        self::assertEquals('jEDT4wQCc1DFwodXNPHFClndTPnFuFmXhBt+isKU4ZpeHeLTENOmvcde0HZDnXtAq/drM3Ncstcx0kNNIfht3g==', $identity['ephemeral_private_signature_key']);
        self::assertEquals('7FSf/n0e76QT3s0DkvetRVVJhXZGEjOxj5EWAFexvjI=', $identity['user_secret']);
    }

    public function testParseValidProvisionalIdentity(): void
    {
        $identity = Internal\tanker_deserialize_identity(self::PROVISIONAL_IDENTITY);

        self::assertEquals(self::APP_ID, $identity['trustchain_id']);
        self::assertEquals('email', $identity['target']);
        self::assertEquals(self::USER_EMAIL, $identity['value']);
        self::assertEquals('W7QEQBu9FXcXIpOgq62tPwBiyFAbpT1rAruD0h/NrTA=', $identity['public_signature_key']);
        self::assertEquals('UmnYuvdTaLYG0a+JaDpY6ojw4/2Ll8zsmramVC4fuqRbtARAG70Vdxcik6Crra0/AGLIUBulPWsCu4PSH82tMA==', $identity['private_signature_key']);
        self::assertEquals('/2j4dI3r8PlvCN3uW4HhA5wBtMKOcACd38K6N0q+mFU=', $identity['public_encryption_key']);
        self::assertEquals('4QB5TWmvcBrgeyDDLhULINU6tbqAOEQ8v9pjDkPcybA=', $identity['private_encryption_key']);
    }

    public function testParseValidPublicIdentity(): void
    {
        $identity = Internal\tanker_deserialize_identity(self::PUBLIC_IDENTITY);

        self::assertEquals(self::APP_ID, $identity['trustchain_id']);
        self::assertEquals('user', $identity['target']);
        self::assertEquals(self::HASHED_USER_ID, $identity['value']);
    }

    public function testParseValidPublicProvisionalIdentity(): void
    {
        $identity = Internal\tanker_deserialize_identity(self::PROVISIONAL_IDENTITY);

        self::assertEquals(self::APP_ID, $identity['trustchain_id']);
        self::assertEquals('email', $identity['target']);
        self::assertEquals(self::USER_EMAIL, $identity['value']);
        self::assertEquals('W7QEQBu9FXcXIpOgq62tPwBiyFAbpT1rAruD0h/NrTA=', $identity['public_signature_key']);
        self::assertEquals('/2j4dI3r8PlvCN3uW4HhA5wBtMKOcACd38K6N0q+mFU=', $identity['public_encryption_key']);
    }

    protected function assertUserSecret(array $identity): void
    {
        $hashed_user_id = base64_decode($identity['value']);
        $user_secret = base64_decode($identity['user_secret']);
        self::assertEquals(32, strlen($hashed_user_id));
        self::assertEquals(32, strlen($user_secret));

        $control_byte = $user_secret[31];
        $control = sodium_crypto_generichash(substr($user_secret, 0, -1) . $hashed_user_id, '', SODIUM_CRYPTO_GENERICHASH_BYTES_MIN);
        self::assertEquals($control[0], $control_byte);
    }

    protected function assertSignature(array $identity, string $app_public_key): void
    {
        $signed_data = base64_decode($identity['ephemeral_public_signature_key']) . base64_decode($identity['value']);

        $signature = base64_decode($identity['delegation_signature']);
        $public_key = base64_decode($app_public_key);
        assertTrue(sodium_crypto_sign_verify_detached($signature, $signed_data, $public_key));
    }

    public function testCreatePermanentIdentity(): void
    {
        $id = create_identity(self::APP_ID, self::APP_SECRET, self::USER_ID);
        $id = Internal\tanker_deserialize_identity($id);

        $keys = array_keys($id);
        sort($keys);
        self::assertEquals(['delegation_signature', 'ephemeral_private_signature_key', 'ephemeral_public_signature_key', 'target', 'trustchain_id', 'user_secret', 'value'], $keys);
        self::assertEquals(self::APP_ID, $id['trustchain_id']);
        self::assertEquals('user', $id['target']);
        $this->assertUserSecret($id);
        $this->assertSignature($id, self::APP_PUBLIC_KEY);
    }

    public function testCreatePermanentIdentityInvalidArguments(): void
    {
        // Invalid b64
        $this->assertException(\InvalidArgumentException::class, function () {
            create_identity("NOT BASE64", self::APP_SECRET, self::USER_ID);
        });
        $this->assertException(\InvalidArgumentException::class, function () {
            create_identity(self::APP_ID, "NOT BASE64", self::USER_ID);
        });

        // Garbage input
        $this->assertException(\InvalidArgumentException::class, function () {
            create_identity(self::LOREM_IPSUM_B64, self::APP_SECRET, self::USER_ID);
        });
        $this->assertException(\InvalidArgumentException::class, function () {
            create_identity(self::APP_ID, self::LOREM_IPSUM_B64, self::USER_ID);
        });
    }

    public function testCreatePermanentIdentityMismatch(): void
    {
        $this->assertException(\InvalidArgumentException::class, function () {
            $mismatchedAppId = 'rB0/yEJWCUVYRtDZLtXaJqtneXQOsCSKrtmWw+V+ysc=';
            create_identity($mismatchedAppId,self::APP_SECRET, self::USER_ID);
        });
    }

    public function testCreateProvisionalIdentity(): void
    {
        $id = create_provisional_identity(self::APP_ID, self::USER_EMAIL);
        $id = Internal\tanker_deserialize_identity($id);

        $keys = array_keys($id);
        sort($keys);
        self::assertEquals(['private_encryption_key', 'private_signature_key', 'public_encryption_key', 'public_signature_key', 'target', 'trustchain_id', 'value'], $keys);
        self::assertEquals(self::APP_ID, $id['trustchain_id']);
        self::assertEquals('email', $id['target']);
        self::assertEquals(self::USER_EMAIL, $id['value']);
    }

    public function testCreateProvisionalIdentityInvalidArguments(): void
    {
        // Invalid b64
        $this->assertException(\InvalidArgumentException::class, function () {
            create_provisional_identity("NOT BASE64", self::USER_ID);
        });

        // Garbage input
        $this->assertException(\InvalidArgumentException::class, function () {
            create_provisional_identity(self::LOREM_IPSUM_B64, self::USER_ID);
        });
    }

    public function testGetPublicIdentity(): void
    {
        $pub_id = get_public_identity(self::PERMANENT_IDENTITY);
        $pub_id = Internal\tanker_deserialize_identity($pub_id);

        $perm_id = Internal\tanker_deserialize_identity(self::PERMANENT_IDENTITY);

        $keys = array_keys($pub_id);
        sort($keys);
        self::assertEquals(['target', 'trustchain_id', 'value'], $keys);
        self::assertEquals(self::APP_ID, $pub_id['trustchain_id']);
        self::assertEquals('user', $pub_id['target']);
        self::assertEquals($perm_id['value'], $pub_id['value']);
    }

    public function testGetPublicIdentityInvalidArgument(): void
    {
        // Invalid b64
        $this->assertException(\InvalidArgumentException::class, function () {
            get_public_identity("NOT BASE64");
        });

        // Garbage input
        $this->assertException(\InvalidArgumentException::class, function () {
            get_public_identity(self::LOREM_IPSUM_B64);
        });
    }

    public function testGetPublicIdentityFromProvisionalIdentity(): void
    {
        $provisional_id_b64 = create_provisional_identity(self::APP_ID, self::USER_EMAIL);
        $provisional_id = Internal\tanker_deserialize_identity($provisional_id_b64);

        $public_id = get_public_identity($provisional_id_b64);
        $public_id = Internal\tanker_deserialize_identity($public_id);

        $keys = array_keys($public_id);
        sort($keys);
        self::assertEquals(['public_encryption_key', 'public_signature_key', 'target', 'trustchain_id', 'value'], $keys);
        self::assertEquals(self::APP_ID, $public_id['trustchain_id']);
        self::assertEquals('email', $public_id['target']);
        self::assertEquals(self::USER_EMAIL, $public_id['value']);
        self::assertEquals($provisional_id['public_encryption_key'], $public_id['public_encryption_key']);
        self::assertEquals($provisional_id['public_signature_key'], $public_id['public_signature_key']);
    }

    public function testOrderedJson(): void
    {
        $object = ["c" => 2, "b" => null, "a" => ["x" => 'y']];
        $expected_json = '{"a":{"x":"y"},"b":null,"c":2}';
        $b64json = Internal\tanker_serialize_identity($object);
        $identity_json = base64_decode($b64json);
        self::assertEquals($identity_json, $expected_json);
    }
}

