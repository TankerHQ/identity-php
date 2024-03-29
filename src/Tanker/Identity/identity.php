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
        throw new \InvalidArgumentException('The app_secret does not match the app_id');
    }

    $app_sign_secretkey = $raw_app_secret;
    $sign_keypair = sodium_crypto_sign_keypair();
    $hashed_user_id = Internal\tanker_hash_user_id($raw_app_id, $user_id);
    $message = sodium_crypto_sign_publickey($sign_keypair) . $hashed_user_id;
    $signature = sodium_crypto_sign_detached($message, $app_sign_secretkey);
    $user_secret = Internal\tanker_generate_user_secret($hashed_user_id);

    $identity_json = array(
        'trustchain_id' => $app_id,
        'target' => 'user',
        'value' => base64_encode($hashed_user_id),
        'delegation_signature' => base64_encode($signature),
        'ephemeral_public_signature_key' => base64_encode(sodium_crypto_sign_publickey($sign_keypair)),
        'ephemeral_private_signature_key' => base64_encode(sodium_crypto_sign_secretkey($sign_keypair)),
        'user_secret' => base64_encode($user_secret),
    );
    return Internal\tanker_serialize_identity($identity_json);
}

/**
 * @param string $app_id The app ID, you can access it from the Tanker dashboard
 * @param string $target The type of provisional identity to create
 * @param string $value The target-specific identifier associated with the provisional identity
 * @return string A provisional identity
 * @throws \InvalidArgumentException
 * @throws \SodiumException
 */
function create_provisional_identity(string $app_id, string $target, string $value): string
{
    $raw_app_id = base64_decode($app_id, true);
    if ($raw_app_id === false || strlen($raw_app_id) !== Internal\TANKER_BLOCK_HASH_SIZE) {
        throw new \InvalidArgumentException('Invalid app_id argument to create_provisional_identity');
    }

    if ($target !== 'email' && $target !== 'phone_number')
        throw new \InvalidArgumentException('Unsupported provisional identity target');

    $encrypt_keypair = sodium_crypto_box_keypair();
    $encrypt_pk = sodium_crypto_box_publickey($encrypt_keypair);
    $encrypt_sk = sodium_crypto_box_secretkey($encrypt_keypair);
    $sign_keypair = sodium_crypto_sign_keypair();
    $sign_pk = sodium_crypto_sign_publickey($sign_keypair);
    $sign_sk = sodium_crypto_sign_secretkey($sign_keypair);

    $identity_json = array(
        'trustchain_id' => $app_id,
        'target' => $target,
        'value' => $value,
        'public_encryption_key' => base64_encode($encrypt_pk),
        'private_encryption_key' => base64_encode($encrypt_sk),
        'public_signature_key' => base64_encode($sign_pk),
        'private_signature_key' => base64_encode($sign_sk),
    );
    return Internal\tanker_serialize_identity($identity_json);
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
        'trustchain_id' => $id_json['trustchain_id'],
        'target' => $id_json['target'],
        'value' => $id_json['value'],
    );

    if ($id_json['target'] === 'user') {
        // OK, nothing to add
    } else {
        if ($id_json['target'] === 'email') {
            $pub_id_json['target'] = 'hashed_email';
            $pub_id_json['value'] = Internal\tanker_hash_provisional_identity_email($id_json['value']);
        } else {
            $pub_id_json['target'] = 'hashed_' . $pub_id_json['target'];
            $pub_id_json['value'] = Internal\tanker_hash_provisional_identity_value($id_json['value'], $id_json['private_signature_key']);
        }

        $pub_id_json['public_encryption_key'] = $id_json['public_encryption_key'];
        $pub_id_json['public_signature_key'] = $id_json['public_signature_key'];
    }

    return Internal\tanker_serialize_identity($pub_id_json);
}

function tanker_upgrade_identity(string $identity_b64): string
{
    $identity = Internal\tanker_deserialize_identity($identity_b64);

    if ($identity['target'] === 'email' && !isset($identity['private_encryption_key'])) {
        $identity['target'] = 'hashed_email';
        $identity['value'] = Internal\tanker_hash_provisional_identity_email($identity['value']);
    }

    return Internal\tanker_serialize_identity($identity);
}
