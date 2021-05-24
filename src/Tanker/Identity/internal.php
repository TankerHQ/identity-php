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


function tanker_to_ordered_json(array $obj): string
{
    $keys = array_keys($obj);
    asort($keys);
    $json = [];
    foreach ($keys as $k) {
        $val = null;
        if (is_array($k) && tanker_array_is_assoc($k)) {
            $val = tanker_to_ordered_json($obj[$k]);
        } else {
            $val = json_encode($obj[$k]);
        }
        array_push($json, "\"$k\":$val");
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

