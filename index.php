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

function getMoralisData($endpoint) {
    global $moralisApiKey, $client;
    $response = $client->get("https://deep-index.moralis.io/api/v2.2/$endpoint", [
        'headers' => [
            'X-API-Key' => $moralisApiKey
        ]
    ]);
    return json_decode($response->getBody(), true);
}

function handleWebhook($update) {
    if (isset($update['message'])) {
        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'];

        if ($text == '/start') {
            $buttons = [
                [['text' => 'Wallet', 'callback_data' => 'wallet']],
                [['text' => 'NFT', 'callback_data' => 'nft']],
                [['text' => 'DeFi', 'callback_data' => 'defi']],
                [['text' => 'Token', 'callback_data' => 'token']],
            ];
            sendMessage($chatId, "Pilih kategori untuk informasi lebih lanjut:", $buttons);
        }
    } elseif (isset($update['callback_query'])) {
        $chatId = $update['callback_query']['message']['chat']['id'];
        $data = $update['callback_query']['data'];

        if ($data == 'wallet') {
            $buttons = [
                [['text' => 'Get Wallet Token Balances', 'callback_data' => 'wallet_token_balances']],
                [['text' => 'Get Wallet Token Approvals', 'callback_data' => 'wallet_token_approvals']],
                [['text' => 'Get Wallet PnL', 'callback_data' => 'wallet_pnl']],
                [['text' => 'Get Wallet Details', 'callback_data' => 'wallet_details']],
                [['text' => 'Get Linea Name Service Domains', 'callback_data' => 'wallet_lns']],
            ];
            sendMessage($chatId, "Pilih opsi Wallet:", $buttons);
        } elseif ($data == 'nft') {
            $buttons = [
                [['text' => 'Get NFTs', 'callback_data' => 'get_nfts']],
                [['text' => 'Get NFT Metadata', 'callback_data' => 'get_nft_metadata']],
                [['text' => 'Get NFT Prices', 'callback_data' => 'get_nft_prices']],
                [['text' => 'Get NFT Trades', 'callback_data' => 'get_nft_trades']],
                [['text' => 'Get NFT Stats', 'callback_data' => 'get_nft_stats']],
                [['text' => 'Get NFT Traits and Rarity', 'callback_data' => 'get_nft_traits']],
            ];
            sendMessage($chatId, "Pilih opsi NFT:", $buttons);
        } elseif ($data == 'defi') {
            $buttons = [
                [['text' => 'Get DeFi Positions on Pancakeswap v2', 'callback_data' => 'defi_positions_pancakeswap']],
            ];
            sendMessage($chatId, "Pilih opsi DeFi:", $buttons);
        } elseif ($data == 'token') {
            $buttons = [
                [['text' => 'Get Token Price', 'callback_data' => 'token_price']],
                [['text' => 'Get Token Approvals', 'callback_data' => 'token_approvals']],
                [['text' => 'Get Token Top Traders', 'callback_data' => 'token_top_traders']],
                [['text' => 'Get Token Pairs & Liquidity', 'callback_data' => 'token_pairs']],
                [['text' => 'Get Token Stats', 'callback_data' => 'token_stats']],
                [['text' => 'Get Token Owners', 'callback_data' => 'token_owners']],
            ];
            sendMessage($chatId, "Pilih opsi Token:", $buttons);
        } else {
            $result = "Hasil pencarian tidak ditemukan";
            switch ($data) {
                case 'wallet_token_balances':
                    $result = getMoralisData(":address/erc20?chain=linea");
                    break;
                case 'wallet_token_approvals':
                    $result = getMoralisData("wallets/:address/approvals?chain=linea");
                    break;
                case 'wallet_pnl':
                    $result = getMoralisData("wallets/:address/profitability/summary?chain=linea");
                    break;
                case 'wallet_details':
                    $result = getMoralisData("wallets/:address/chains?chain=linea");
                    break;
                case 'wallet_lns':
                    $result = getMoralisData("resolve/:address/reverse?chain=linea");
                    break;
                case 'get_nfts':
                    $result = getMoralisData(":address/nft?chain=linea");
                    break;
                case 'get_nft_metadata':
                    $result = getMoralisData("nft/:address/:token_id?chain=linea");
                    break;
                case 'get_nft_prices':
                    $result = getMoralisData("nft/:address/:token_id/floor-price?chain=linea");
                    break;
                case 'get_nft_trades':
                    $result = getMoralisData("nft/:address/:token_id/trades?chain=linea");
                    break;
                case 'get_nft_stats':
                    $result = getMoralisData("nft/:address/:token_id/stats?chain=linea");
                    break;
                case 'get_nft_traits':
                    $result = getMoralisData("nft/:address/traits?chain=linea");
                    break;
                case 'defi_positions_pancakeswap':
                    $result = getMoralisData("wallets/:address/defi/pancakeswap-v2/positions?chain=linea");
                    break;
                case 'token_price':
                    $result = getMoralisData("erc20/:address/price");
                    break;
                case 'token_approvals':
                    $result = getMoralisData("wallets/:address/approvals");
                    break;
                case 'token_top_traders':
                    $result = getMoralisData("erc20/:address/top-gainers");
                    break;
                case 'token_pairs':
                    $result = getMoralisData(":token_address/pairs/stats");
                    break;
                case 'token_stats':
                    $result = getMoralisData("erc20/:address/stats");
                    break;
                case 'token_owners':
                    $result = getMoralisData("erc20/:token_address/owners");
                    break;
            }
            sendMessage($chatId, json_encode($result, JSON_PRETTY_PRINT));
        }
    }
}

$input = file_get_contents('php://input');
$update = json_decode($input, true);
handleWebhook($update);
?>
