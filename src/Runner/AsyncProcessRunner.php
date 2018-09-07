
<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

use Amp\ByteStream\Payload;
use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Loop;
use Amp\Process\Process;
use NextDom\Helper\Logger;
/**
 * Wrapper around Process with Amp
 */
namespace NextDom\Runner;
class AsyncProcessRunner
{
    /** @var string command output */
    private $output;
    /** @var int command exit code */
    private $exitCode;
    /** @var string executable to run */
    private $command;
    /** @var array arguments for that executable */
    private $arguments;
    public function __construct(string $command, array $arguments)
    {
        $this->exitCode  = -1;
        $this->output    = null;
        $this->command   = $command;
        $this->arguments = $arguments;
    }
    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }
    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }
    /**
     * @param string $output
     */
    public function setOutput(string $output): void
    {
        $this->output = $output;
    }
    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }
    /**
     * @param int $exitCode
     */
    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
    /**
     * Run the process and set output
     */
    public function run(): void
    {
        $self       = &$this; // goofiness to get the output values out
        $lambdaLoop = function () use (&$self) {
            $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
            $logHandler->setFormatter(new ConsoleFormatter);
            $logger = new Logger('server');
            $logger->pushHandler($logHandler);
            $command = $self->getCommand() . ' ' . \implode(' ', $self->getArguments());
            $logger->debug("Process command: {$command}");
            $process = new Process($command);
            $process->start();
            $payload = new Payload($process->getStdout());
            $output  = yield $payload->buffer();
            $self->setOutput($output);
            $logger->debug("Process Output: {$self->getOutput()}");
            $exitCode = yield $process->join();
            $self->setExitCode($exitCode);
            $logger->debug("Exit code: {$self->getExitCode()}");
            Loop::stop();
            if ($self->getExitCode() !== 0) {
                throw new \Exception("PactPHP Process returned non-zero exit code: {$self->getExitCode()}");
            }
        };
        Loop::run($lambdaLoop);
    }
}