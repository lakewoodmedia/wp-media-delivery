<?php

namespace WP_Media_Delivery;

use WP_Media_Delivery\Abstracts\S3_Provider;
use WP_Media_Delivery\Traits\OffloaderTrait;
use WP_Media_Delivery\Interfaces\ObserverInterface;
use WP_Media_Delivery\Observers\AttachmentUrlObserver;
use WP_Media_Delivery\Observers\AttachmentDeleteObserver;
use WP_Media_Delivery\Observers\OffloadStatusObserver;
use WP_Media_Delivery\Observers\ImageSrcsetObserver;
use WP_Media_Delivery\Observers\ImageSrcsetMetaObserver;
use WP_Media_Delivery\Observers\AttachmentUploadObserver;
use WP_Media_Delivery\Observers\PostContentImageTagObserver;
use WP_Media_Delivery\Observers\AttachmentUpdateObserver;

class Offloader
{
	use OffloaderTrait;

	private static $instance = null;
	public $cloudProvider;
	private array $observers = [];
	private function __construct(S3_Provider $cloudProvider)
	{
		$this->cloudProvider = $cloudProvider;
	}

	public static function get_instance(S3_Provider $cloudProvider)
	{
		if (self::$instance === null) {
			self::$instance = new self($cloudProvider);
		}
		return self::$instance;
	}

	public function initializeHooks()
	{
		$this->attach(new AttachmentUploadObserver($this->cloudProvider));
		$this->attach(new ImageSrcsetObserver($this->cloudProvider));
		$this->attach(new ImageSrcsetMetaObserver($this->cloudProvider));
		$this->attach(new AttachmentUrlObserver($this->cloudProvider));
		$this->attach(new OffloadStatusObserver($this->cloudProvider));
		$this->attach(new AttachmentDeleteObserver($this->cloudProvider));
		$this->attach(new PostContentImageTagObserver($this->cloudProvider));
		$this->attach(new AttachmentUpdateObserver($this->cloudProvider));

		foreach ($this->observers as $observer) {
			$observer->register();
		}
	}

	public function attach(ObserverInterface $observer)
	{
		$this->observers[] = $observer;
	}

	public function detach(ObserverInterface $observer)
	{
		foreach ($this->observers as $key => $obs) {
			if ($obs === $observer) {
				unset($this->observers[$key]);
			}
		}
	}
}
