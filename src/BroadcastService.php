<?php

namespace App;

class BroadcastService
{
    private PriceService $priceService;
    private FarmerService $farmerService;
    private SmsService $smsService;
    private LogService $logService;

    public function __construct()
    {
        $this->priceService = new PriceService();
        $this->farmerService = new FarmerService();
        $this->smsService = new SmsService();
        $this->logService = new LogService();
    }

    /**
     * Executes the full broadcast process: fetch, format, send, and log.
     *
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function executeBroadcast(): array
    {
        // 1. Fetch data
        $latestPrices = $this->priceService->getLatestPrices();
        if (empty($latestPrices)) {
            return ['success' => false, 'message' => 'Broadcast failed: No prices found in the database for today.'];
        }

        $farmers = $this->farmerService->getAllFarmers();
        if (empty($farmers)) {
            return ['success' => false, 'message' => 'Broadcast failed: No farmers are registered in the system.'];
        }

        // 2. Group farmers by preferred language
        $farmersByLanguage = [];
        foreach ($farmers as $farmer) {
            $lang = $farmer['preferred_language'] ?? 'en'; // Default to English if not set
            $farmersByLanguage[$lang][] = $farmer['phone_number'];
        }

        // 3. Process each language group
        $overallSuccess = true;
        $resultsSummary = [];
        $totalSent = 0;

        foreach ($farmersByLanguage as $lang => $recipients) {
            // 3a. Format the language-specific message
            $message = $this->formatMessageForLanguage($lang, $latestPrices);

            // 3b. Send the bulk SMS for this group
            $result = $this->smsService->sendBulk($recipients, $message);
            $recipientCount = count($recipients);

            if ($result['success']) {
                $totalSent += $recipientCount;
                $logStatus = 'success';
                $logMessage = "Broadcast to {$recipientCount} '{$lang}' users successful. Message: {$message}";
            } else {
                $overallSuccess = false;
                $logStatus = 'failure';
                $logMessage = "Broadcast to {$recipientCount} '{$lang}' users failed. API Response: " . $result['message'];
            }
            $this->logService->logBroadcast($logMessage, $logStatus);
        }

        if ($overallSuccess) {
            return ['success' => true, 'message' => "Broadcast initiated successfully to {$totalSent} farmers across all languages."];
        } else {
            return ['success' => false, 'message' => 'One or more language groups failed to send. Please check the logs for details.'];
        }
    }

    /**
     * Formats the price alert message for a specific language.
     *
     * @param string $lang The language code (e.g., 'en', 'ruk').
     * @param array $prices The array of latest prices.
     * @return string The formatted message.
     */
    private function formatMessageForLanguage(string $lang, array $prices): string
    {
        $header = ($lang === 'ruk')
            ? "Omuhendo gwe emondi erizoba (buli bafu):"
            : "Today's Irish Potato Prices (per basin):";

        $messageParts = [$header];
        foreach ($prices as $priceInfo) {
            $messageParts[] = strtoupper($priceInfo['market_name']) . ' - ' . number_format($priceInfo['price']) . ' UGX';
        }

        return implode("\n", $messageParts);
    }
}