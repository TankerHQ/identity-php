<?php declare(strict_types=1);
namespace Tanker\Identity\Internal;

const TANKER_BLOCK_HASH_SIZE = 32;
const TANKER_USER_SECRET_SIZE = 32;
const TANKER_APP_SECRET_SIZE = 64;
const TANKER_APP_PUBLIC_KEY_SIZE = 32;

function tanker_hash_user_id(string $app_id, string $user_id): string
{
    return sodium_crypto_generichash($user_id . $app_id, '', TANKER_BLOCK_HASH_SIZE);
}

function tanker_hash_provisional_identity_email(string $email): string
{
    return base64_encode(sodium_crypto_generichash($email, '', TANKER_BLOCK_HASH_SIZE));
}

function tanker_hash_provisional_identity_value(string $value, string $private_signature_key): string
{
    $private_signature_key_binary = base64_decode($private_signature_key, true);
    if ($private_signature_key_binary === false)
        throw new \InvalidArgumentException('Invalid identity private signature key');
    $hash_salt = sodium_crypto_generichash($private_signature_key_binary, '', TANKER_BLOCK_HASH_SIZE);
    return base64_encode(sodium_crypto_generichash($hash_salt . $value, '', TANKER_BLOCK_HASH_SIZE));
}

function tanker_generate_app_id(string $app_secret): string
{
    $app_creation_nature = "\x01";
    $author = str_repeat("\0", TANKER_BLOCK_HASH_SIZE);
    $pubkey = substr($app_secret, -TANKER_APP_PUBLIC_KEY_SIZE);
    return sodium_crypto_generichash($app_creation_nature . $author . $pubkey, '', TANKER_BLOCK_HASH_SIZE);
}

function tanker_generate_user_secret(string $hashed_user_id): string
{
    $random = random_bytes(TANKER_USER_SECRET_SIZE - 1);
    $hashed_byte = sodium_crypto_generichash($random . $hashed_user_id, '', SODIUM_CRYPTO_GENERICHASH_BYTES_MIN);
    return $random . $hashed_byte[0];
}

// https://stackoverflow.com/questions/173400
function tanker_array_is_assoc(array $arr)
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function tanker_json_sort($a, $b)
{
    $json_order = [
        "trustchain_id" => 1,
        "target" => 2,
        "value" => 3,
        "delegation_signature" => 4,
        "ephemeral_public_signature_key" => 5,
        "ephemeral_private_signature_key" => 6,
        "user_secret" => 7,
        "public_encryption_key" => 8,
        "private_encryption_key" => 9,
        "public_signature_key" => 10,
        "private_signature_key" => 11,
    ];
    return $json_order[$a] - $json_order[$b];
}

function tanker_to_ordered_json(array $obj): string
{
    $keys = array_keys($obj);
    usort($keys, "Tanker\\Identity\\Internal\\tanker_json_sort");
    $json = [];
    foreach ($keys as $k) {
        array_push($json, "\"$k\":\"$obj[$k]\"");
    }
    $result = join(",", $json);
    return "{{$result}}";
}

function tanker_deserialize_identity(string $identity): array
{
    $id_json = base64_decode($identity, true);
    if ($id_json === false) {
        throw new \InvalidArgumentException('Invalid identity argument');
    }
    $id_json = json_decode($id_json, true);
    if ($id_json === null) {
        throw new \InvalidArgumentException('Invalid identity argument');
    }
    return $id_json;
}

function tanker_serialize_identity(array $identity): string
{
    return base64_encode(tanker_to_ordered_json($identity));
}

