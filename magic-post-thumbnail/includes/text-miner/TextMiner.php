<?php
//Text Analysis Tools
//Author: Matt Smith
//Supports NGrams

/*
require __DIR__ . '/vendor/autoload.php';
use Wamania\Snowball\French;
*/


class TextMiner {

    public static $version = "2.0";
    private $text;
    private $N;
    private $unigrams;
    private $nGrams;
    private $nGramCounts;

    //Options
    public $removeStopWords;
    public $includeLowerNGrams;
    public $convertToLower;
    const stop_words_file="stop_words.txt";
    const verbose=FALSE;

    public function __construct(){
        $this->clear();
    }
    public function addText($text){
        $this->text .= ' || '.$text;
    }
    public function addFile($filename){
        $text = file_get_contents($filename);
        if($text!=FALSE) {
            return $this->addText($text);
        } else {
            return false;
        }
    }
    public function clear(){
        $this->text='';
        $this->N=2; //default to bi-grams
        $this->unigrams=array();
        $this->nGrams=array();
        $this->nGramCounts=array();
        $this->processed=FALSE;
        $this->removeStopWords=TRUE;
        $this->includeLowerNGrams=FALSE;
        $this->convertToLower=FALSE;
    }

    public function process(){
        $this->cleanText();
        $this->identifyNGrams();
        $this->countNGrams();
        $this->processed = TRUE;
    }
    public function setN($N){
        $this->N = $N;
    }
    public function getN(){
        return $this->N;
    }
    public function setText($text){
        $this->text = $text;
    }
    public function getText(){
        return $this->text;
    }
    public function setNGrams($nGrams){
        $this->nGrams = $nGrams;
    }
    private function addNGrams($nGrams){
        foreach($nGrams as $nGram){
            $this->nGrams[] = $nGram;
        }
    }
    public function getNGrams(){
        return $this->nGrams;
    }
    public function setNGramCounts($nGramCounts){
        arsort($nGramCounts);
        $this->nGramCounts = $nGramCounts;
    }
    public function getNGramCounts(){
        if(!$this->processed) return "Run process first.";
        return $this->nGramCounts;
    }
    public function getTopNGrams($n=10,$as_array=TRUE){
        $results = array_slice($this->nGramCounts,0,$n,TRUE);
        if($as_array) {
            return $results;
        } else {
            return implode(', ',array_keys($results));
        }
    }
    public function printSummary(){
        echo "======================<br/>";
        echo "Text: <b>".trim(substr($this->getText(),0,200))."...</b><br/>";
        echo "Total nGrams: <b>".count($this->getNGrams())."</b><br/>";
        echo "======================<br/>";
    }

    //PRIVATE METHODS
    private function cleanText() {


		//$search_to_replace = $this->getText();
		// Replace accent
		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$search_to_replace = strtr( $this->getText(), $unwanted_array );


        $searchReplace = array(

            //REMOVALS
            "'<script[^>]*?>.*?</script>'si" => " " //Strip out Javascript
            , "'<style[^>]*?>.*?</style>'si" => " " //Strip out Styles
            , "'<[/!]*?[^<>]*?>'si" => " " //Strip out HTML tags
            //ACCEPT ONLY
            , "/[^a-zA-Z0-9\-' ]/" => " " //only accept these characters

        );
        foreach($searchReplace as $s=>$r){
            $search[]=$s;
            $replace[]=$r;
        }
        $this->setText(utf8_encode($search_to_replace));
        $this->setText(html_entity_decode($search_to_replace));
        if($this->convertToLower) $this->setText(strtolower($search_to_replace));
        //$this->setText(strip_tags($this->text));
        //if(self::verbose) { echo "<hr>BEFORE<hr><pre>"; echo $this->getText(); echo "</pre>";}
        $this->setText(preg_replace($search, $replace, $search_to_replace));
        //if(self::verbose) { echo "<hr>AFTER<hr><pre>"; print_r( preg_split('/\s+/',$this->getText()) ); echo "</pre>";}
    }
    private function identifyNGrams($N=null) {
        if($N==null) $N=$this->N;
        $numUnigrams = count($this->unigrams);
        if($numUnigrams==0) {
            $this->identifyUnigrams();
            $numUnigrams = count($this->unigrams);
        }
        if($N>1){
            $nGrams = array();
            for($i=($N-1); $i<$numUnigrams; $i++){
                $nGram = "";
                for($j=0; $j<$N; $j++){
                    $nGram = $this->unigrams[$i-$j].' '.trim($nGram);
                }
                $nGrams[] = trim($nGram);
            }
        } else {
            $nGrams = $this->unigrams;
        }
        //if($this->removeStopWords) $nGrams = $this->removeStopWords($nGrams);
        $this->addNGrams($nGrams);
        if($this->includeLowerNGrams && $N>1) {
            $this->identifyNGrams($N-1);
        }
    }
    private function identifyUnigrams(){
        $unigrams = preg_split('/\s+/',trim($this->getText()));
        if($this->removeStopWords) {
            $this->unigrams = $this->removeStopWords($unigrams);
        } else {
            $this->unigrams=$unigrams;
        } // printa($this->unigrams);
    }
    private function countNGrams() {
        $nGramCounts = array_count_values($this->getNGrams());
        /*if(1||$this->removeRedundantLesserGrams){
            arsort($nGramCounts);
            foreach($nGramCounts as $k=>$v){
                echo "$k:$v\n";
            }
        }*/
        $this->setNGramCounts($nGramCounts);
    }

    //STATIC METHODS - can be called without having an instance of TextMiner
    //removeStopWords: removes the stopwords from a referenced array
    public static function removeStopWords(&$words) { //expects an array ([0] = w1, [1] = w2, etc.)
        $numWordsIn = count($words);
        if(self::verbose) { echo "removedStopWords => wordcount (IN: ".$numWordsIn.") "; }

        // Get lang option
        $options       = wp_parse_args( get_option( 'MPT_plugin_main_settings' ) );
        $selected_lang = $options['text_analyser_lang'];


        $languages = array( 'ar','bg','cs','da','de','el','en','es','et','fa','fi','fr','he','hi','hr','hu','hy','id','it','ja','ko','lt','lv','nl','no','pl','pt','ro','ru','sk','sl','sv','th','tr','vi','zh' );

        if( in_array( $selected_lang, $languages ) ) {
            $stopwords_path = plugin_dir_path( __FILE__ ) . 'stop-words/' . $selected_lang . '.txt';
        } else {
            $stopwords_path = plugin_dir_path( __FILE__ ) . 'stop-words/en.txt';
        }

        if($stopwords_path) {
            $stopWords = explode("\n",strtolower(file_get_contents($stopwords_path)));
        } else {
            $stopWords = array("","the","and","a","of","by","although","i","to","in","on","at","but","or","nor","for","-");
        }

        $trim_stopWords = array_map('trim', $stopWords); // Important : add trim() to remove characters like \r into txt files
        $words = array_diff($words,$trim_stopWords);
        $words = array_values($words);//re-indexes array
        $numWordsOut = count($words);

        return $words;
    }

    public static function getStemCounts(&$words,$minSupport=5) {

		//require("Stemming.php");
        $stems = array();

        foreach($words as $word) {
            if(substr($word,0,1) != "#" && strlen($word)) { //indicates a comment

                $string =strtolower($word); //string to be stemmed

				/*
                $stm = new Stemming();
                $stm->reset();
                for($i=0;$i<strlen($string);$i++)
                $stm->add(substr($string,$i,1));
                $stm->stem(0);
                $stem = $stm->toString(); //stem of the string
			*/

				$stm = new French();
				$stem = $stm->stem($string);






                if(self::verbose) {
                    echo "<b>$string</b> => <b style='color: blue;'>$stem</b><br/>";
                    if(!is_numeric($stem) && !is_string($stem)) echo "[".$stem."]";
                }
                if(array_key_exists($stem,$stems)) {
                    $stems[$stem] .= ", ".$string;
                    $stems_ctr[$stem]++;
                } else {
                    $stems[$stem] = $string;
                    $stems_ctr[$stem] = 1;
                }
            }
        }
        arsort($stems_ctr);
        return $stems_ctr;
    }
    public static function outputStemTable(&$words,$minSupport=5){
        $stems_ctr = self::getStemCounts($words,$minSupport);

        echo "<table style='border: 1px solid #aaa;'><tr style='background: #aa0000; color: white; font-weight: bold;'><td>STEM</td><td>WORDS</td></tr>";
		//var_dump( $stems_ctr);
        foreach($stems_ctr as $stem=>$word_count) {
			//var_dump( $stem );
			//var_dump( $word_count );
			//var_dump( $minSupport );
			//echo '<hr>';

			//if($word_count>=$minSupport) echo "<tr><td style='background: #efefef; font-weight: bold;'>".$stem."</td><td>".$stems[$stem]." (".$word_count.")<td/></tr>";
			if($word_count>=$minSupport) echo "<tr><td style='background: #efefef; font-weight: bold;'>".$stem."</td><td>".$stems_ctr[$stem]." (".$word_count.")<td/></tr>";
        }
        echo "</table>";
    }
}

if(!function_exists('printa')) {
    function printa($array){
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }
}


?>
