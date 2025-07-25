<?php
namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AmazonSESController {
	public  $smtpObj;
	private $use_fallback_smtp = false;

	public function __construct( $smtpObj ) {
		$this->use_fallback_smtp = Utils::conditionUseFallbackSmtp();
		if( $this->use_fallback_smtp ) {
			$settings = Utils::getYaySmtpSetting();
			Utils::setFromFallback($smtpObj, $settings);
		} else { 
			Utils::setFrom($smtpObj);
		}

		$this->smtpObj = $smtpObj;
	}

	/**
	 * Use Amazon SES API Services to send emails.
	 */
	public function send() {
		$dataLogsDB           = Utils::prepareDataLogInit( $this->smtpObj );
		$dataLogsDB['mailer'] = 'AmazonSES';
		$logId = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $this->smtpObj, $logId);

		try {
			$data = array(
				'RawMessage' => array(
					'Data' => $this->prepare(),
				),
			);

			$amazonSevice = new AmazonWebServicesController();
			$result       = $amazonSevice->getClient()->sendRawEmail( $data );
			$messId       = $result->get( 'MessageId' );

			$sent = false;
			if ( ! empty( $messId ) ) {
				$sent = true;
			}
			if ( $sent ) {
				if ( $this->use_fallback_smtp ) {
					LogErrors::clearErrFallback();
				} else {
					LogErrors::clearErr();
				}

				if ( ! empty( $logId ) ) { 
					$updateData['id']        = $logId;
					$updateData['date_time'] = current_time( 'mysql', true );
					$updateData['status']    = 1;
					Utils::updateEmailLog( $updateData );
				}
			} 

			return $sent;

		} catch ( \Exception $e ) {
			$errMess = $e->getMessage();

			if ( $this->use_fallback_smtp ) {
				LogErrors::clearErrFallback();
				LogErrors::setErrFallback( $errMess );
			} else {
				LogErrors::clearErr();
				LogErrors::setErr( 'Mailer: Amazon SES' );
				LogErrors::setErr( $errMess );
			}

			if ( ! empty( $logId ) ) {
				$updateData['id'] = $logId;
				if ( ! empty( $errMess ) ) {
					$extra_info               = Utils::getExtraInfo( $logId );
					$extra_info['error_mess'] = $errMess;		
					$updateData['extra_info'] = wp_json_encode($extra_info);
				}
				Utils::updateEmailLog( $updateData );
			}

			return;
		}
	}

	private function prepare() {
		$this->smtpObj->Mailer = 'mail'; 
		try {
			$this->smtpObj->preSend();
		} catch ( \Exception $exception ) {
			return $exception;
		}

		return $this->smtpObj->getSentMIMEMessage();
	}
}
