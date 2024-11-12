<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

$telegramToken = '7803409599:AAGJL64U5ahZyiiCcwvB4C95vXQvHHZXdbo';
$moralisApiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJub25jZSI6ImMzOGEwMDRmLWZiN2YtNDc0Mi1iODY0LTNlMjJkZjFiMjYzNiIsIm9yZ0lkIjoiNDE1NDY4IiwidXNlcklkIjoiNDI2OTgxIiwidHlwZUlkIjoiYTMwZmYyNmMtNGU0OC00YTQ0LTg2MmEtMmJlMGZmMGU0NDdlIiwidHlwZSI6IlBST0pFQ1QiLCJpYXQiOjE3MzExNDIxODcsImV4cCI6NDg4NjkwMjE4N30.RuooKtDNumak-ycuFQfiYPYxpDaNOcSqydxBHmNUf6w';
$client = new Client();

function sendMessage($chatId, $text, $buttons = []) {
    global $telegramToken;
    $url = "https://api.telegram.org/bot$telegramToken/sendMessage";

    $keyboard = $buttons ? json_encode(['inline_keyboard' => $buttons]) : '';
    $client = new Client();
    $client->post($url, [
        'json' => [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $keyboard
        ]
    ]);
}

function answerCallbackQuery($callbackId, $text) {
    global $telegramToken;
    $url = "https://api.telegram.org/bot$telegramToken/answerCallbackQuery";
    $client = new Client();
    $client->post($url, [
        'json' => [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => false
        ]
    ]);
}

function fetchData($url) {
    global $moralisApiKey;
    $client = new Client();
    $response = $client->request('GET', $url, [
        'headers' => ['X-API-Key' => $moralisApiKey]
    ]);
    return json_decode($response->getBody(), true);
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Process input from Telegram
$update = json_decode(file_get_contents("php://input"), TRUE);

if (isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];

    if ($text == "/start") {
        sendMessage($chatId, "Pilih opsi:", [
            [['text' => "Wallet", 'callback_data' => 'wallet']],
            [['text' => "NFT", 'callback_data' => 'nft']],
            [['text' => "DeFi", 'callback_data' => 'defi']],
            [['text' => "Token", 'callback_data' => 'token']]
        ]);
    }
} elseif (isset($update["callback_query"])) {
    $callbackId = $update["callback_query"]["id"];
    $chatId = $update["callback_query"]["message"]["chat"]["id"];
    $data = $update["callback_query"]["data"];

    answerCallbackQuery($callbackId, "Anda memilih $data.");

    if ($data == "wallet") {
        sendMessage($chatId, "Masukkan alamat wallet:");
    } elseif ($data == "nft") {
        sendMessage($chatId, "Masukkan alamat kontrak NFT:");
    } elseif ($data == "defi") {
        sendMessage($chatId, "Masukkan alamat wallet untuk posisi Pancakeswap v2:");
    } elseif ($data == "token") {
        sendMessage($chatId, "Masukkan alamat token:");
    }
}

// Wallet function
if (isset($text) && preg_match('/^wallet (.+)$/', $text, $match)) {
    $walletAddress = $match[1];

    $balances = fetchData("https://deep-index.moralis.io/api/v2.2/$walletAddress/erc20?chain=linea");
    sendMessage($chatId, "Token Balances: " . json_encode($balances));

    $approvals = fetchData("https://deep-index.moralis.io/api/v2.2/wallets/$walletAddress/approvals?chain=linea");
    sendMessage($chatId, "Token Approvals: " . json_encode($approvals));

    $pnl = fetchData("https://deep-index.moralis.io/api/v2.2/wallets/$walletAddress/profitability/summary?chain=linea");
    sendMessage($chatId, "PnL Summary: " . json_encode($pnl));

    $details = fetchData("https://deep-index.moralis.io/api/v2.2/wallets/$walletAddress/chains?chain=linea");
    sendMessage($chatId, "Wallet Details: " . json_encode($details));

    $nameService = fetchData("https://deep-index.moralis.io/api/v2.2/resolve/$walletAddress/reverse?chain=linea");
    sendMessage($chatId, "Linea Name Service Domains: " . json_encode($nameService));
}

// NFT function
if (isset($text) && preg_match('/^nft (.+)$/', $text, $match)) {
    $nftAddress = $match[1];

    $nfts = fetchData("https://deep-index.moralis.io/api/v2.2/$nftAddress/nft?chain=linea");
    sendMessage($chatId, "NFTs: " . json_encode($nfts));

    $metadata = fetchData("https://deep-index.moralis.io/api/v2.2/nft/$nftAddress/metadata?chain=linea");
    sendMessage($chatId, "NFT Metadata: " . json_encode($metadata));

    $owners = fetchData("https://deep-index.moralis.io/api/v2.2/nft/$nftAddress/owners?chain=linea");
    sendMessage($chatId, "NFT Owners: " . json_encode($owners));

    $prices = fetchData("https://deep-index.moralis.io/api/v2.2/nft/$nftAddress/floor-price?chain=linea");
    sendMessage($chatId, "NFT Prices: " . json_encode($prices));

    $trades = fetchData("https://deep-index.moralis.io/api/v2.2/nft/$nftAddress/trades?chain=linea");
    sendMessage($chatId, "NFT Trades: " . json_encode($trades));

    $stats = fetchData("https://deep-index.moralis.io/api/v2.2/nft/$nftAddress/stats?chain=linea");
    sendMessage($chatId, "NFT Stats: " . json_encode($stats));

    $traits = fetchData("https://deep-index.moralis.io/api/v2.2/nft/$nftAddress/traits?chain=linea");
    sendMessage($chatId, "NFT Traits and Rarity: " . json_encode($traits));
}

// DeFi function
if (isset($text) && preg_match('/^defi (.+)$/', $text, $match)) {
    $walletAddress = $match[1];
    $defiPositions = fetchData("https://deep-index.moralis.io/api/v2.2/wallets/$walletAddress/defi/pancakeswap-v2/positions?chain=linea");
    sendMessage($chatId, "DeFi Positions: " . json_encode($defiPositions));
}

// Token function
if (isset($text) && preg_match('/^token (.+)$/', $text, $match)) {
    $tokenAddress = $match[1];

    $price = fetchData("https://deep-index.moralis.io/api/v2.2/erc20/$tokenAddress/price");
    sendMessage($chatId, "Token Price: " . json_encode($price));

    $approvals = fetchData("https://deep-index.moralis.io/api/v2.2/wallets/$tokenAddress/approvals");
    sendMessage($chatId, "Token Approvals: " . json_encode($approvals));

    $topTraders = fetchData("https://deep-index.moralis.io/api/v2.2/erc20/$tokenAddress/top-gainers");
    sendMessage($chatId, "Token Top Traders: " . json_encode($topTraders));

    $pairs = fetchData("https://deep-index.moralis.io/api/v2.2/$tokenAddress/pairs/stats");
    sendMessage($chatId, "Token Pairs & Liquidity: " . json_encode($pairs));

    $stats = fetchData("https://deep-index.moralis.io/api/v2.2/erc20/$tokenAddress/stats");
    sendMessage($chatId, "Token Stats: " . json_encode($stats));

    $owners = fetchData("https://deep-index.moralis.io/api/v2.2/erc20/$tokenAddress/owners");
    sendMessage($chatId, "Token Owners: " . json_encode($owners));
}
?>
