<?php

namespace WP_Media_Delivery\Integrations;

use WP_Media_Delivery\Abstracts\S3_Provider;
use WPFitter\Aws\S3\S3Client;

class DigitalOceanSpaces extends S3_Provider
{
	public $providerName = "DigitalOcean Spaces";

	public function __construct()
	{
		// Do nothing.
	}

	public function getProviderName()
	{
		return $this->providerName;
	}

	public function getClient()
	{
		return new S3Client([
			'version' => 'latest',
			'endpoint' => defined("WPMD_DOS_ENDPOINT") ? WPMD_DOS_ENDPOINT : '',
			'region' => defined("WPMD_DOS_REGION") ? WPMD_DOS_REGION : 'us-east-1',
			'credentials' => [
				'key' => defined("WPMD_DOS_KEY") ? WPMD_DOS_KEY : '',
				'secret' => defined("WPMD_DOS_SECRET") ? WPMD_DOS_SECRET : '',
			]
		]);
	}

	public function getBucket()
	{
		return defined("WPMD_DOS_BUCKET") ? WPMD_DOS_BUCKET : null;
	}

	public function getDomain()
	{
		return defined('WPMD_DOS_DOMAIN') ? trailingslashit(WPMD_DOS_DOMAIN) : '';
	}

	public function credentialsField()
	{
		$requiredConstants = [
			'WPMD_DOS_KEY' => 'Your DigitalOcean Spaces Access Key',
			'WPMD_DOS_SECRET' => 'Your DigitalOcean Spaces Secret Key',
			'WPMD_DOS_ENDPOINT' => 'Your DigitalOcean Spaces Endpoint URL',
			'WPMD_DOS_BUCKET' => 'Your DigitalOcean Spaces Bucket Name',
			'WPMD_DOS_DOMAIN' => 'Your Custom Domain',
		];

		echo $this->getCredentialsFieldHTML($requiredConstants);
	}
}
