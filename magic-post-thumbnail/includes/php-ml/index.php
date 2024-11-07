<?php

require_once 'vendor/autoload.php';

use Phpml\Tokenization\WordTokenizer;

class KeywordExtractor
{
    private $stopWords;
    private $ngramRange;
    private $tokenizer;

    /**
     * Constructor to initialize stop words and the tokenizer.
     * Selects the appropriate stop words file based on the specified language.
     *
     * @param string $selected_lang Language code for stop words (default: 'en').
     */
    public function __construct($selected_lang = 'en')
    {
        // Supported language codes for stop words
        $languages = array('ar','bg','cs','da','de','el','en','es','et','fa','fi','fr','he','hi','hr','hu','hy','id','it','ja','ko','lt','lv','nl','no','pl','pt','ro','ru','sk','sl','sv','th','tr','vi','zh');

        // Select the stop words file based on the chosen language or default to English
        if (in_array($selected_lang, $languages)) {
            $stopWordsFile = plugin_dir_path(__FILE__) . 'stop-words/' . $selected_lang . '.txt';
        } else {
            $stopWordsFile = plugin_dir_path(__FILE__) . 'stop-words/en.txt';
        }

        // Load and prepare stop words as lowercase
        $this->stopWords = array_map('strtolower', array_map('trim', file($stopWordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));

        // Initialize the tokenizer
        $this->tokenizer = new WordTokenizer();
    }

    /**
     * Extracts keywords from the provided text and returns the top results.
     *
     * @param string $text The input text for keyword extraction.
     * @param int $topResults Number of top keywords to return (default: 10).
     * @return array List of most relevant keywords.
     */
    public function extractKeywords($text, $topResults = 10)
    {
        // Clean the text before processing
        $cleanedText = $this->cleanPostContent($text);

        // Tokenize and filter words by removing stop words
        $words = $this->tokenizer->tokenize($cleanedText);
        $filteredWords = array_filter($words, fn($word) => !in_array(strtolower($word), $this->stopWords));
        
        // Count unigrams, bigrams, and trigrams
        $unigrams = array_count_values($filteredWords);
        $bigrams = array_count_values($this->generateNgrams($filteredWords, 2));
        $trigrams = array_count_values($this->generateNgrams($filteredWords, 3));

        // Combine the counts of unigrams, bigrams, and trigrams
        $combinedCounts = [];
        $this->mergeCounts($unigrams, $combinedCounts);
        $this->mergeCounts($bigrams, $combinedCounts);
        $this->mergeCounts($trigrams, $combinedCounts);

        // Return top keywords based on weighted counts
        return $this->getTopKeywords($combinedCounts, count($words), $topResults);
    }

    /**
     * Cleans the content by removing HTML tags, Gutenberg comments, and unnecessary spaces.
     *
     * @param string $content The raw content to clean.
     * @return string The cleaned content.
     */
    private function cleanPostContent($content)
    {
        // Remove Gutenberg comments
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Remove all HTML tags
        $content = strip_tags($content);
        
        // Remove multiple spaces and unnecessary newlines
        $content = preg_replace('/\s+/', ' ', $content);

        // Remove spaces &nbsp; 
        $content = str_replace('&nbsp;', ' ', $content);
        
        // Trim leading and trailing whitespace
        return trim($content);
    }

    /**
     * Generates n-grams (bigrams or trigrams) from the list of words, excluding any that contain stop words.
     *
     * @param array $words List of filtered words.
     * @param int $n Number of words in each n-gram.
     * @return array Unique n-grams as strings.
     */
    private function generateNgrams($words, $n)
    {
        $ngrams = [];
        for ($i = 0; $i <= count($words) - $n; $i++) {
            $ngram = array_slice($words, $i, $n);

            // Ensure the n-gram contains no stop words and no repeated words
            if (count(array_intersect(array_map('strtolower', $ngram), $this->stopWords)) === 0 && count(array_unique($ngram)) === count($ngram)) {
                $ngrams[] = implode(' ', $ngram);
            }
        }
        return array_unique($ngrams);
    }

    /**
     * Merges word counts into a combined array, adding counts if the word already exists.
     *
     * @param array $counts Array of word counts to merge.
     * @param array &$combinedCounts Reference to the array where counts are combined.
     */
    private function mergeCounts($counts, &$combinedCounts)
    {
        foreach ($counts as $phrase => $count) {
            $combinedCounts[$phrase] = ($combinedCounts[$phrase] ?? 0) + $count;
        }
    }

    /**
     * Calculates and retrieves the top keywords based on weighted counts.
     *
     * @param array $combinedCounts Combined counts of unigrams, bigrams, and trigrams.
     * @param int $totalWords Total number of words in the cleaned text.
     * @param int $topResults Number of top results to retrieve.
     * @return array Top keywords sorted by weight.
     */
    private function getTopKeywords($combinedCounts, $totalWords, $topResults)
    {
        $weightedCounts = [];
        foreach ($combinedCounts as $phrase => $count) {
            // Weight based on phrase length for higher relevance
            $lengthWeight = strlen($phrase) / 10;
            $weightedCounts[$phrase] = ($count / $totalWords) * $lengthWeight;
        }

        // Sort by descending weight and return the top results
        arsort($weightedCounts);
        return array_keys(array_slice($weightedCounts, 0, $topResults, true));
    }
}
?>