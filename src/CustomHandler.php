<?php

namespace Shockedplot7560\MonologCustomHandler;

use InvalidArgumentException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

class CustomHandler extends AbstractProcessingHandler {
    /** @var resource */
    private $file;

    /** @throws InvalidArgumentException */
    public function __construct(string $filePath, int|string $level = Logger::DEBUG, bool $bubble = true) {
        $this->initialize($filePath);
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record) : void {
        fwrite($this->file, $this->formatString($record) . PHP_EOL);
        fwrite(STDERR, $this->formatString($record, true) . PHP_EOL);
    }

    private function formatString(LogRecord $record, bool $colored = false): string{
        $notice = $colored ? ColoredLevel::getTerminalColor(LogLevel::NOTICE) : "";
        $color = $colored ? ColoredLevel::getTerminalColor($record->level) : "";
        $reset = $colored ? ColoredLevel::getReset() : "";
        $pattern = "[".$notice."%s$reset] ".$color."[%s %s]: %s$reset";
        return sprintf($pattern,
            $record->datetime->format('Y-m-d H:i:s.v'),
            $record->channel,
            $record->level->getName(),
            $record->message
        );
    }

    private function initialize(string $filePath): void {
        if(!file_exists($filePath)) {
            file_put_contents($filePath, '');
        }
        $file = fopen($filePath, 'a');
        if(!$file) {
            throw new InvalidArgumentException('Could not open file');
        }
        $this->file = $file;
    }
}