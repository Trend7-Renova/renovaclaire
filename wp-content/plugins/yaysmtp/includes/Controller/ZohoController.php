<?php

namespace YaySMTP\Controller;

use YaySMTP\Helper\LogErrors;
use YaySMTP\Helper\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ZohoController {
	public $apiLink = '';
	public $smtpObj;
	public $body = array();
	private $headers = [];
	private $data_center = 'zoho.com';

	public function __construct( $smtpObj ) {
		Utils::setFrom($smtpObj);
		$this->smtpObj     = $smtpObj;
		$this->data_center = ZohoServiceVendController::getDataCenter(); 
		$this->apiLink     = 'https://mail.' . $this->data_center . '/api/accounts/6575615000000008002/messages';
	}

	/**
	 * Get Account Id to use it in Zoho send mail api apiLink
	 */
	public function get_account_id() {
		$yst = Utils::getYaySmtpSetting();

		$response = wp_remote_get(
			'https://mail.' . $this->data_center . '/api/accounts',
			array(
				'headers' => array(
					'Authorization' => 'Zoho-oauthtoken ' . $yst['zoho']['access_token'],
				),
			)
		);

		$response_body = json_decode( $response['body'] );

		if ( 200 !== $response_body->status->code ) {

			return '';

		} else {

			$account_id = $response_body->data[0]->accountId;
			return $account_id;

		}
	}

	/**
	 * Set Zoho send mail apiLink with Account Id
	 */
	public function setUrl( $account_id ) {

		if ( '' !== $account_id && isset( $account_id ) ) {
			$this->apiLink = 'https://mail.' . $this->data_center . '/api/accounts/' . $account_id . '/messages';
		} else {
			$this->apiLink = 'https://mail.' . $this->data_center . '/api/accounts//messages';
		}
	}

	/**
	 * Override send method
	 */
	public function send() {
		// Insert Log - start
		$dataLogsDB           = Utils::prepareDataLogInit( $this->smtpObj );
		$dataLogsDB['mailer'] = 'Zoho';
		$logId 				  = Utils::insertEmailLogs( $dataLogsDB );

		do_action('yaysmtp_send_before', $this->smtpObj, $logId);
		// Insert Log - end

		$this->setUrl( $this->get_account_id() );

		if ( ZohoServiceVendController::isDiffInfo() ) {
			ZohoServiceVendController::doResetToken();
		} elseif ( ZohoServiceVendController::isExpired() ) {
			$regenerate_url   = 'https://accounts.' . $this->data_center . '/oauth/v2/token?';
			$regenerate_url  .= 'refresh_token=' . ZohoServiceVendController::getSetting( 'refresh_token' );
			$regenerate_url  .= '&client_id=' . ZohoServiceVendController::getSetting( 'client_id' );
			$regenerate_url  .= '&client_secret=' . ZohoServiceVendController::getSetting( 'client_secret' );
			$regenerate_url  .= '&grant_type=refresh_token';
			$response         = wp_remote_post( $regenerate_url );
			$response         = json_decode( $response['body'] );
			$new_access_token = $response->access_token;
			Utils::setYaySmtpSetting( 'access_token', $new_access_token, 'zoho' );
			Utils::setYaySmtpSetting( 'created_at', strtotime( 'now' ), 'zoho' );
		}
		$yst = Utils::getYaySmtpSetting();

		$this->headers['Authorization'] = 'Zoho-oauthtoken ' . $yst['zoho']['access_token'];
		$this->headers['Content-Type']  = 'application/json';
		$this->headers['accept']        = 'application/json';
		$this->body                     = array_merge( $this->body, array( 'subject' => $this->smtpObj->Subject ) );
		$this->body                     = array_merge( $this->body, array( 'fromAddress' => '"' . $this->smtpObj->FromName . '" <' . $this->smtpObj->From . '>' ) );
		$this->body                     = array_merge( $this->body, array( 'content' => $this->smtpObj->Body ) );

		update_option( 'Shrief', $this->smtpObj->getToAddresses() );
		$optionShrief = get_option( 'Shrief' );
		if ( ! empty( $optionShrief ) && is_array( $optionShrief ) ) {
			$dataTo = [];
			foreach ( $optionShrief as $toEmail ) {
				if ( empty( $toEmail[1] ) ) {
					$dataTo[] = $toEmail[0];
				} else {
					$dataTo[] = sprintf( '%s <%s>', $toEmail[1], $toEmail[0] );
				}
			}

			if ( ! empty( $dataTo ) ) {
				$this->body   = array_merge( $this->body, array( 'toAddress' => implode( ",", $dataTo ) ) );
			}
		}

		$ccAddresses = $this->smtpObj->getCcAddresses();
		if ( ! empty( $ccAddresses ) && is_array( $ccAddresses ) ) {
			$dataCc = [];
			foreach ( $ccAddresses as $ccEmail ) {
				if ( empty( $ccEmail[1] ) ) {
					$dataCc[] = $ccEmail[0];
				} else {
					$dataCc[] = sprintf( '%s <%s>', $ccEmail[1], $ccEmail[0] );
				}
			}

			if ( ! empty( $dataCc ) ) {
				$this->body   = array_merge( $this->body, array( 'ccAddress' => implode( ",", $dataCc ) ) );
			}
		}

		$bccAddresses = $this->smtpObj->getBccAddresses();
		if ( ! empty( $bccAddresses ) && is_array( $bccAddresses ) ) {
			$dataBcc = [];
			foreach ( $bccAddresses as $bccEmail ) {
				if ( empty( $bccEmail[1] ) ) {
					$dataBcc[] = $bccEmail[0];
				} else {
					$dataBcc[] = sprintf( '%s <%s>', $bccEmail[1], $bccEmail[0] );
				}
			}
			
			if ( ! empty( $dataBcc ) ) {
				$this->body   = array_merge( $this->body, array( 'bccAddress' => implode( ",", $dataBcc ) ) );
			}
		}

		// Set attachments.
		$attachments = $this->smtpObj->getAttachments();
		if ( ! empty( $attachments ) ) {
			$headerAttach                 = $this->headers;
			$headerAttach['Content-Type'] = 'application/octet-stream';
			$dataAttachments              = array();

			foreach ( $attachments as $attach ) {
				$attachFile = false;
				try {
					if ( is_file( $attach[0] ) && is_readable( $attach[0] ) ) {
						$attachFile = file_get_contents( $attach[0] );
					}
				} catch ( \Exception $e ) {
					$attachFile = false;
				}

				if ( false === $attachFile ) {
					continue;
				}

				$urlAttach = add_query_arg(
					'fileName',
					$attach[2],
					$this->apiLink . '/attachments'
				);

				$paramsAttach = array(
					'headers' => $headerAttach,
					'body'    => $attachFile,
				);

				$responseAt = wp_safe_remote_post( $urlAttach, $paramsAttach );

				if ( is_wp_error( $responseAt ) || wp_remote_retrieve_response_code( $responseAt ) !== 200 ) {
					continue;
				}

				$responseAttach = json_decode( wp_remote_retrieve_body( $responseAt ), true );

				if ( ! empty( $responseAttach['data'] ) ) {
					$dataAttachments[] = $responseAttach['data'];
				}
			}

			if ( ! empty( $dataAttachments ) ) {
				$this->body = array_merge( $this->body, array( 'attachments' => $dataAttachments ) );
			}
		}

		$params = array_merge(
			array(
				'timeout'     => 15,
				'httpversion' => '1.1',
				'blocking'    => true,
			),
			array(
				'headers' => $this->headers,
				'body'    => $this->getBody(),
			)
		);

		$response = wp_remote_post( $this->apiLink, $params );

		/** Is sent mail. */
		$is_sent  = false;
		$res_body = json_decode( $response['body'] );
		if ( is_object( $res_body ) ) {
			$error = null;
			if ( isset( $res_body->data->errorCode ) ) {
				$error = $res_body->data->errorCode;
			}
			if ( isset( $res_body->data->moreInfo ) ) {
				$error = $res_body->data->moreInfo;
			}
		} else {
			$error = $res_body[1]->errorCode ? $res_body[1]->moreInfo : $res_body[1]->moreInfo;
		}
		if ( 200 === $response['response']['code'] ) {
			$is_sent = true;

			LogErrors::clearErr();
			if ( ! empty( $logId ) ) { 
				$updateData['id']        = $logId;
				$updateData['date_time'] = current_time( 'mysql', true );
				$updateData['status']    = 1;
				Utils::updateEmailLog( $updateData );
			}
		} elseif ( 500 == $response['response']['code'] ) {
			LogErrors::clearErr();
			LogErrors::setErr( 'Please use your Zoho mail to send email. We do not support this type of mail address' );

			$extra_info               = Utils::getExtraInfo( $logId );
			$extra_info['error_mess'] = 'Please use your Zoho mail to send email. We do not support this type of mail address';		
			$updateData['extra_info'] = wp_json_encode($extra_info);
			$updateData['id']         = $logId;
			Utils::updateEmailLog( $updateData );
		} else {
			LogErrors::clearErr();
			LogErrors::setErr( $error );

			if ( ! empty( $logId ) ) {
				$updateData['id']           = $logId;
				$updateData['date_time']    = current_time( 'mysql', true );
				$updateData['reason_error'] = $error;

				if ( ! empty( $error ) ) {
					$extra_info               = Utils::getExtraInfo( $logId );
					$extra_info['error_mess'] = $error;		
					$updateData['extra_info'] = wp_json_encode($extra_info);
				}

				Utils::updateEmailLog( $updateData );
			}
		}

		return $is_sent;

	}

	/**
	 * Set & get some send-mail informations
	 */
	public function getBody() {
		$body = $this->body;
		return wp_json_encode( $body );
	}
}
