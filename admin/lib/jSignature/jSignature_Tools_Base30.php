<?php
/** @license
jSignature v2 SVG export plugin.
Copyright (c) 2012 Willow Systems Corp http://willow-systems.com
MIT License <http://www.opensource.org/licenses/mit-license.php>
*/
defined('_JEXEC') or die('Restricted access');
class jSignature_Tools_Base30 {
    private $chunkSeparator = '';
    private $charmap = array();
    private $charmap_reverse = array(); // will be filled by 'uncompress*" function
    private $allchars = array();
    private $bitness = 0;
    private $minus = '';
    private $plus = '';

    function __construct() {
        $this->allchars = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWX');
        $this->bitness = sizeof($this->allchars) / 2;
        $this->minus = 'Z';
        $this->plus = 'Y';
        $this->chunkSeparator = '_';

        for($i = $this->bitness-1; $i > -1; $i--){
            $this->charmap[$this->allchars[$i]] = $this->allchars[$i+$this->bitness];
            $this->charmap_reverse[$this->allchars[$i+$this->bitness]] = $this->allchars[$i];
        }
    }

    //Decompresses half of a stroke in a base30-encoded jSignature image.
    private function uncompress_stroke_leg($datastring){
        // we convert half-stroke (only 'x' series or only 'y' series of numbers)
        // datastring like this:
        // "5agm12100p1235584210m53"
        // is converted into this:
        // [517,516,514,513,513,513,514,516,519,524,529,537,541,543,544,544,539,536]
        // each number in the chain is converted such:
        // - digit char = start of new whole number. Alpha chars except "p","m" are numbers in hiding.
        //   These consecutive digist expressed as alphas mapped back to digit char.
        //   resurrected number is the diff between this point and prior coord.
        // - running polaritiy is attached to the number.
        // - we undiff (signed number + prior coord) the number.
        // - if char 'm','p', flip running polarity 
        $answer = array();
        $chars = str_split( $datastring );
        $l = sizeof( $chars );
        $ch = '';
        $polarity = 1;
        $partial = array();
        $preprewhole = 0;
        $prewhole = 0;

        for($i = 0; $i < $l; $i++){
            $ch = $chars[$i];
            if (array_key_exists($ch, $this->charmap) || $ch == $this->minus || $ch == $this->plus){
                
                // this is new number - start of a new whole number.
                // before we can deal with it, we need to flush out what we already 
                // parsed out from string, but keep in limbo, waiting for this sign
                // that prior number is done.
                // we deal with 3 numbers here:
                // 1. start of this number - a diff from previous number to 
                //    whole, new number, which we cannot do anything with cause
                //    we don't know its ending yet.
                // 2. number that we now realize have just finished parsing = prewhole
                // 3. number we keep around that came before prewhole = preprewhole

                if (sizeof($partial) != 0) {
                    // yep, we have some number parts in there.
                    $prewhole = intval( implode('', $partial), $this->bitness) * $polarity + $preprewhole;
                    array_push( $answer, $prewhole );
                    $preprewhole = $prewhole;
                }

                if ($ch == $this->minus){
                    $polarity = -1;
                    $partial = array();
                } else if ($ch == $this->plus){
                    $polarity = 1;
                    $partial = array();
                } else {
                    // now, let's start collecting parts for the new number:
                    $partial = array($ch);
                }
            } else /* alphas replacing digits */ {
                // more parts for the new number
                array_push( $partial, $this->charmap_reverse[$ch]);
            }
        }

        array_push( $answer, intval( implode('',$partial), $this->bitness ) * $polarity + $preprewhole );
        
        return $answer;
    }
    //convert base30 to array of points (x,y)
    public function Base64ToNative($datastring){
	    //global $chunkSeparator;

        $data = array();
        $chunks = explode( $this->chunkSeparator, $datastring );
        $l = sizeof($chunks) / 2;
        for ($i = 0; $i < $l; $i++){
            array_push( $data, array(
                'x' => $this->uncompress_stroke_leg($chunks[$i*2])
                , 'y' => $this->uncompress_stroke_leg($chunks[$i*2+1])
            ));
        }
        return $data;
    }

}


?>