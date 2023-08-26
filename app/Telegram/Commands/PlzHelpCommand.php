<?php

namespace App\Telegram\Commands;

class PlzHelpCommand extends Command {
	protected string $name = 'plzhelp';
	protected string $description = 'PLZ HEWLP';
	public bool $hidden = true;

	public function handle(): void {
		$this->replyWithSticker([
			'sticker' => 'CAACAgQAAxkBAAIwql5oyc6UCDU-9CXXba_tcSVHgCyFAAKDAQACh7pZBkRd1KmXSK3rGAQ',
		]);
		$this->replyWithSticker([
			'sticker' => 'CAACAgQAAxkBAAIwrF5oydGd__NtLqeUzJKHLS07m31pAAKKAQACh7pZBorYn1WQba9kGAQ',
		]);
	}
}
