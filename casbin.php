<?php
function casbinEnforce($sub, $obj, $act) {
    $url = "http://localhost:8080/enforce?sub=" . urlencode($sub) . "&obj=" . urlencode($obj) . "&act=" . urlencode($act);
    $response = file_get_contents($url);
    if ($response === false) return false;
    $result = json_decode($response, true);
    return isset($result['allowed']) && $result['allowed'] === true;
}

function casbinAddRoleForUser($user, $role) {
    return casbinPost("http://localhost:8080/role", ['user' => $user, 'role' => $role]);
}

function casbinDeleteRoleForUser($user, $role) {
    $url = "http://localhost:8080/role";
    $data = json_encode(['user' => $user, 'role' => $role]);

    $opts = [
        "http" => [
            "method"  => "DELETE",
            "header"  => "Content-Type: application/json\r\n",
            "content" => $data
        ]
    ];

    $context  = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        return ["error" => "Failed to connect to Casbin server"];
    }

    return json_decode($response, true);
}

function casbinAddPolicy($sub, $obj, $act) {
    return casbinPost("http://localhost:8080/policy", ['sub' => $sub, 'obj' => $obj, 'act' => $act]);
}

function casbinDeletePolicy($sub, $obj, $act) {
    $url = "http://localhost:8080/policy";
    $data = json_encode(['sub' => $sub, 'obj' => $obj, 'act' => $act]);

    $opts = [
        "http" => [
            "method"  => "DELETE",
            "header"  => "Content-Type: application/json\r\n",
            "content" => $data
        ]
    ];

    $context  = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        return ["error" => "Failed to connect to Casbin server"];
    }

    return json_decode($response, true);
}

function casbinGetAllRoles() {
    $response = file_get_contents("http://localhost:8080/roles");
    return $response ? json_decode($response, true) : [];
}

function casbinGetAllPolicies() {
    $response = file_get_contents("http://localhost:8080/policies");
    return $response ? json_decode($response, true) : [];
}

function casbinPost($url, $data) {
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result ? json_decode($result, true) : false;
}
?>
