<?php
function casbinEnforce($sub, $obj, $act) {
    $url = "http://localhost:8080/enforce?sub=" . urlencode($sub) . "&obj=" . urlencode($obj) . "&act=" . urlencode($act);
    $response = file_get_contents($url);
    if ($response === false) return false;

    $result = json_decode($response, true);
    return isset($result['allowed']) && $result['allowed'] === true;
}
?>