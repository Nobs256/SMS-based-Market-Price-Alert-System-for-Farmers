<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SmsService
{
    private Client $client;
    private string $username;
    private string $password;
    private string $senderId;
    private string $apiEndpoint;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10.0]);
        $this->username = Config::EGO_SMS_USERNAME;
        $this->password = Config::EGO_SMS_PASSWORD;
        $this->senderId = Config::EGO_SMS_SENDER_ID;
        $this->apiEndpoint = Config::EGO_SMS_API_ENDPOINT;
    }

    /**
     * Sends a message to multiple recipients via the Ego SMS API.
     *
     * @param array $recipients An array of phone numbers.
     * @param string $message The SMS message content.
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function sendBulk(array $recipients, string $message): array
    {
        if (empty($this->username) || $this->username === 'YOUR_EGO_SMS_USERNAME') {
            return ['success' => false, 'message' => 'API Username is not configured. Please check src/Config.php'];
        }

        if (empty($recipients)) {
            return ['success' => false, 'message' => 'No recipients provided for the SMS broadcast.'];
        }

        $errors = [];
        $successCount = 0;

        // The documented API sends to one number at a time, so we loop.
        foreach ($recipients as $recipient) {
            try {
                $response = $this->client->get($this->apiEndpoint, [
                    'query' => [
                        'username' => $this->username,
                        'password' => $this->password,
                        'sender'   => $this->senderId,
                        'number'   => $recipient,
                        'message'  => $message,
                    ]
                ]);

                $responseBody = trim((string) $response->getBody());

                if (str_starts_with(strtolower($responseBody), 'ok')) {
                    $successCount++;
                } else {
                    $errors[] = "Failed for {$recipient}: API response: {$responseBody}";
                }
            } catch (GuzzleException $e) {
                $errors[] = "Failed for {$recipient}: Request error: " . $e->getMessage();
            }
        }

        if (empty($errors)) {
            return ['success' => true, 'message' => "Successfully sent SMS to all {$successCount} recipients."];
        } else {
            $errorMessage = "Completed with {$successCount} successes and " . count($errors) . " failures. Errors: " . implode('; ', $errors);
            return ['success' => false, 'message' => $errorMessage];
        }
    }
}