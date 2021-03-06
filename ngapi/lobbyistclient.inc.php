<?php
if (strlen($graphTarget) == 11 && is_numeric($graphTarget)) {
    // is ABN

$supplierN = $dbConn->prepare(' SELECT *
FROM lobbyist_clients
WHERE "ABN" = ?
LIMIT 1 ');
$supplierN->execute(array(
    $graphTarget
));
$lobbyistC = $supplierN->fetch(PDO::FETCH_OBJ);
if ($lobbyistC) {
$lobbyistClientID = $lobbyistC->lobbyistClientID;
 $lobbyistClientName = $lobbyistC->business_name;
} else {
    die ("lobbyist abn $graphTarget not found");
}
} else {
    // is business name
$lobbyistClientName = $graphTarget;
$lobbyistClientSearchName = $lobbyistClientName."%";
$supplierN = $dbConn->prepare(' SELECT *
FROM lobbyist_clients
WHERE business_name like ?
LIMIT 1 ');
$supplierN->execute(array(
    $lobbyistClientSearchName
));
$lobbyistC = $supplierN->fetch(PDO::FETCH_OBJ);
if ($lobbyistC) {
$lobbyistClientID = $lobbyistC->lobbyistClientID;
} else {
    die ("lobbyist client $lobbyistClientName not found");
}
}

$lobbyistClientNode = $nodes->addChild('node');
$lobbyistClientNode->addAttribute("id", "lobbyistclient-" . $lobbyistClientName);
$lobbyistClientNode->addAttribute("label", $lobbyistClientName);
formatLobbyingClientNode($lobbyistClientNode);
$xml->addChild('name', htmlspecialchars($lobbyistClientName));

$lobbyists = $dbConn->prepare('
SELECT *
FROM lobbyists
INNER JOIN lobbyist_relationships ON lobbyists."lobbyistID" = lobbyist_relationships."lobbyistID"
WHERE "lobbyistClientID" = ? ;
');
$lobbyists->execute(array(
    $lobbyistClientID
));



foreach ($lobbyists->fetchAll() as $row) {
    $exists = false;
    foreach ($nodes->node as $node) {
        $attributes = $node->attributes();
        if ($attributes['id'] == "lobbyist-" . $row['abn']) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $node = $nodes->addChild('node');
        $head_node_id = "lobbyist-" . $row['abn'];
        $node->addAttribute("id", $head_node_id);
        $node->addAttribute("label", "Lobbyist: " . $row['business_name']);
        formatLobbyistNode($node);
    }
    $link = $edges->addChild('edge');
    $tail_node_id = "lobbyistclient-" . $lobbyistClientName;
    $link->addAttribute("id", $head_node_id . "|" . $tail_node_id);
    $link->addAttribute("tooltip", $row['business_name'] . " lobbies for " . $lobbyistClientName);
    $link->addAttribute("tail_node_id", $tail_node_id);
    $link->addAttribute("head_node_id", $head_node_id);
}
// donations

$searchName = searchName($lobbyistClientName);
$result = $dbConn->prepare('select min("DonorClientNm") as "DonorClientNm",min("RecipientClientNm") as "RecipientClientNm",min("DonationDt") as "DonationDt",sum("AmountPaid") as "AmountPaid" from political_donations where "DonorClientNm"
			       LIKE ? group by "RecipientClientNm" order by "RecipientClientNm" desc');
$result->execute(array(
    $searchName
));

foreach ($result->fetchAll() as $row) {
    $exists = false;
    foreach ($nodes->node as $node) {
        $attributes = $node->attributes();
        if ($attributes['id'] == "donationrecipient-" . $row['RecipientClientNm']) {
            $exists = true;
            break;
        }
    }
    $head_node_id = "donationrecipient-" . $row['RecipientClientNm'];
    if (!$exists) {
        $node = $nodes->addChild('node');
        $node->addAttribute("id", $head_node_id);
        $node->addAttribute("label", "Donation Recipient: " . $row['RecipientClientNm']);
        formatLobbyistNode($node);
    }
    $link = $edges->addChild('edge');
    $tail_node_id = "lobbyistclient-" . $lobbyistClientName;
    $link->addAttribute("id", $head_node_id . "|" . $tail_node_id);
    $link->addAttribute("tooltip", $lobbyistClientName . " donated $" . money_format('%i', $row['AmountPaid']) . " to " . $row['RecipientClientNm']);
    $link->addAttribute("tail_node_id", $tail_node_id);
    $link->addAttribute("head_node_id", $head_node_id);
}
?>
