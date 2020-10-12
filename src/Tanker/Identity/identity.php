<?php declare(strict_types=1);
namespace Tanker\Identity;

/**
 * @param string $app_id The app ID, you can access it from the Tanker dashboard
 * @param string $app_secret The app secret, secret that you have saved right after the creation of your app
 * @param string $user_id The unique ID of a user in your application
 * @return string An identity
 * @throws \InvalidArgumentException
 * @throws \SodiumException
 */
function create_identity(string $app_id, string $app_secret, string $user_id): string
{
    $raw_app_id = base64_decode($app_id, true);
    $raw_app_secret = base64_decode($app_secret, true);
    if ($raw_app_id === false || strlen($raw_app_id) !== Internal\TANKER_BLOCK_HASH_SIZE) {
        throw new \InvalidArgumentException('Invalid app_id argument to create_identity');
    }
    if ($raw_app_secret === false || strlen($raw_app_secret) !== Internal\TANKER_APP_SECRET_SIZE) {
        throw new \InvalidArgumentException('Invalid app_secret argument to create_identity');
    }

    if ($raw_app_id != Internal\tanker_generate_app_id($raw_app_secret)) {
        throw new \InvalidArgumentException("The app_secret does not match the app_id");
    }

    $app_sign_secretkey = $raw_app_secret;
    $sign_keypair = sodium_crypto_sign_keypair();
    $hashed_user_id = Internal\tanker_hash_user_id($raw_app_id, $user_id);
    $message = sodium_crypto_sign_publickey($sign_keypair) . $hashed_user_id;
    $signature = sodium_crypto_sign_detached($message, $app_sign_secretkey);
    $user_secret = Internal\tanker_generate_user_secret($hashed_user_id);

    $identity_json = array(
        "trustchain_id" => $app_id,
        "target" => "user",
        "value" => base64_encode($hashed_user_id),
        "delegation_signature" => base64_encode($signature),
        "ephemeral_public_signature_key" => base64_encode(sodium_crypto_sign_publickey($sign_keypair)),
        "ephemeral_private_signature_key" => base64_encode(sodium_crypto_sign_secretkey($sign_keypair)),
        "user_secret" => base64_encode($user_secret),
    );
    return base64_encode(json_encode($identity_json));
}

/**
 * @param string $app_id The app ID, you can access it from the Tanker dashboard
 * @param string $email The email associated with the provisional identity
 * @return string A provisional identity
 * @throws \InvalidArgumentException
 * @throws \SodiumException
 */
function create_provisional_identity(string $app_id, string $email): string
{
    $raw_app_id = base64_decode($app_id, true);
    if ($raw_app_id === false || strlen($raw_app_id) !== Internal\TANKER_BLOCK_HASH_SIZE) {
        throw new \InvalidArgumentException('Invalid app_id argument to create_provisional_identity');
    }

    $encrypt_keypair = sodium_crypto_box_keypair();
    $encrypt_pk = sodium_crypto_box_publickey($encrypt_keypair);
    $encrypt_sk = sodium_crypto_box_secretkey($encrypt_keypair);
    $sign_keypair = sodium_crypto_sign_keypair();
    $sign_pk = sodium_crypto_sign_publickey($sign_keypair);
    $sign_sk = sodium_crypto_sign_secretkey($sign_keypair);

    $identity_json = array(
        "trustchain_id" => $app_id,
        "target" => "email",
        "value" => $email,
        "public_encryption_key" => base64_encode($encrypt_pk),
        "private_encryption_key" => base64_encode($encrypt_sk),
        "public_signature_key" => base64_encode($sign_pk),
        "private_signature_key" => base64_encode($sign_sk),
    );
    return base64_encode(json_encode($identity_json));
}

/**
 * @param string $identity An identity or a provisional identity
 * @return string A public identity
 * @throws \InvalidArgumentException
 */
function get_public_identity(string $identity): string
{
    $id_json = Internal\tanker_deserialize_identity($identity);

    $pub_id_json = array(
        "trustchain_id" => $id_json["trustchain_id"],
        "target" => $id_json["target"],
        "value" => $id_json["value"],
    );

    switch ($id_json["target"]) {
        case "user":
            break; // OK, nothing to add
        case "email":
            $pub_id_json["public_encryption_key"] = $id_json["public_encryption_key"];
            $pub_id_json["public_signature_key"] = $id_json["public_signature_key"];
            break;
        default:
            throw new \InvalidArgumentException('Unsupported identity type: ' . $id_json["target"]);
    }

    return base64_encode(json_encode($pub_id_json));
}
