<?php


class EmailService
{
    private $sendgrid;
    private $senderEmail;
    private $senderName;
    private $templateId;

    public function __construct()
    {
        try {
            require_once("./libs/sendgrid-php/sendgrid-php.php");

            // Log API key check
            //$apiKey = "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";
           

            $this->sendgrid = new \SendGrid($apiKey);
            $this->senderEmail = "chbinoumed06@gmail.com";
            $this->senderName = "Mohamed";
            $this->templateId = "d-be09ebf49c4f4b9980c1d96b0d51d9c8";

            error_log("EmailService initialized successfully");
        } catch (Exception $e) {
            error_log("EmailService initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendBookingConfirmation($recipientEmail, $recipientName, $bookingData)
    {
        try {
            // Log incoming data
            error_log("Attempting to send email to: " . $recipientEmail);
            error_log("Booking data: " . print_r($bookingData, true));

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->senderEmail, $this->senderName);
            $email->setSubject("Meeting Room Booking Confirmation");
            $email->addTo($recipientEmail, $recipientName);
            $email->setTemplateId($this->templateId);

            $email->addDynamicTemplateDatas($bookingData);

            // Log before sending
            error_log("About to send email via SendGrid");

            $response = $this->sendgrid->send($email);

            // Log response
            error_log("SendGrid Response Status Code: " . $response->statusCode());
            error_log("SendGrid Response Body: " . $response->body());

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                error_log("Email sent successfully");
                return [
                    'success' => true,
                    'status' => $response->statusCode(),
                    'message' => 'Email sent successfully'
                ];
            } else {
                error_log("Email sending failed with status code: " . $response->statusCode());
                return [
                    'success' => false,
                    'status' => $response->statusCode(),
                    'message' => 'Failed to send email: ' . $response->body()
                ];
            }
        } catch (Exception $e) {
            error_log("Exception while sending email: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
