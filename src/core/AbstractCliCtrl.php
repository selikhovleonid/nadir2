<?php

namespace nadir2\core;

/**
 * This is an abstract class of cli-controller.
 * @author Leonid Selikhov
 */
class AbstractCliCtrl extends AbstractCtrl
{
    /** This map consists of predefined font colors.*/
    protected const FONT_COLOR_MAP = [
        'black'      => '0;30',
        'lightRed'   => '1;31',
        'red'        => '0;31',
        'yellow'     => '1;33',
        'lightGreen' => '1;32',
        'green'      => '0;32',
        'lightBlue'  => '1;34',
        'blue'       => '0;34',
        'white'      => '1;37',
    ];
    
    /** This map consists of predefined background colors.*/
    protected const BG_COLOR_MAP   = [
        'black'  => '40',
        'red'    => '41',
        'yellow' => '43',
        'green'  => '42',
        'blue'   => '44',
    ];

    /**
     * The method returns options from command line argument list.
     * @param array $opt An array of short options (for ex.  -f)
     * @param array $longOpt An array of long options (for ex.  --foo)
     * @return array|false It returns false if an error occures.
     */
    protected function getCmdOpt(array $opt, array $longOpt = [])
    {
        return getopt(implode('', $opt), $longOpt);
    }

    /**
     * It returns console colored message.
     * @param string $msg The input string.
     * @param string|null $fontColor The passed font color.
     * @param string|null $bgColor The passed background color.
     * @return string
     */
    private function getColoredMsg(
        string $msg,
        ?string $fontColor,
        ?string $bgColor
    ): string {
        $res = '';
        if (!is_null($fontColor)) {
            $res .= "\033[{$fontColor}m";
        }
        if (!is_null($bgColor)) {
            $res .= "\033[{$bgColor}m";
        }
        if (!is_null($fontColor) || !is_null($bgColor)) {
            return "{$res}{$msg}\033[0m";
        }
        return $msg;
    }

    /**
     * It prints passed message to the console.
     * @param string $msg Message to print.
     * @param bool $withTime Flag to print with previous timestamp.
     * @return void
     */
    protected function print(string $msg, bool $withTime = true): void
    {
        $preMsg = '';
        if ($withTime) {
            $preMsg = (new \DateTime('now'))->format('H:i:s')."\t";
        }
        echo "{$preMsg}{$msg}".\PHP_EOL;
    }

    /**
     * Method prints passed error to the console.
     * @param \Throwable $error Throwable error or exception.
     * @param bool $withTime Flag to print with previous timestamp.
     * @param string|null $fontColor The font color.
     * @param string|null $bgColor The background color.
     * @return void
     */
    protected function printError(
        \Throwable $error,
        bool $withTime = true,
        ?string $fontColor = null,
        ?string $bgColor = self::BG_COLOR_MAP['red']
    ): void {
        $shift = $withTime ? "\t\t" : '';
        $this->print(
            $this->getColoredMsg(
                'Error: '.$error->getMessage(),
                $fontColor,
                $bgColor
            ).\PHP_EOL
            .$shift.$this->getColoredMsg(
                'File: '.$error->getFile(),
                $fontColor,
                $bgColor
            ).\PHP_EOL
            .$shift.$this->getColoredMsg(
                'Line: '.$error->getLine(),
                $fontColor,
                $bgColor
            ),
            $withTime
        );
    }

    /**
     * The method prints important information to the console.
     * @param type $msg Message to print.
     * @param bool $withTime Flag to print with previous timestamp.
     * @param string|null $fontColor The font color.
     * @param string|null $bgColor The background color.
     * @return void
     */
    protected function printInfo(
        $msg,
        bool $withTime = true,
        ?string $fontColor = self::FONT_COLOR_MAP['lightGreen'],
        ?string $bgColor = null
    ): void {
        $this->print($this->getColoredMsg($msg, $fontColor, $bgColor), $withTime);
    }

    /**
     * It prints warning message to the console.
     * @param type $msg Message to print.
     * @param bool $withTime Flag to print with previous timestamp.
     * @param string|null $fontColor The font color.
     * @param string|null $bgColor The background color.
     * @return void
     */
    protected function printWarning(
        $msg,
        bool $withTime = true,
        ?string $fontColor = self::FONT_COLOR_MAP['yellow'],
        ?string $bgColor = null
    ): void {
        $this->print($this->getColoredMsg($msg, $fontColor, $bgColor), $withTime);
    }
}
