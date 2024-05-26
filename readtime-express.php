<?php
/*
Plugin Name: ReadTime Express
Description: Add word count and reading time to content.
Version: 1.0.0
Author: Yeasin Arafat
Text Domain: readtime-express
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the translation files
function rte_plugin_init() {
    load_plugin_textdomain( 'readtime-express', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'rte_plugin_init' );

// Add word count and reading time to content
function rte_add_wordcount_readingtime_to_content( $content ) {
    if ( is_single() ) {
        global $post;

        // Get post content
        $post_content = $post->post_content;

        // Calculate word count
        $word_count = str_word_count( wp_strip_all_tags( $post_content ) );

        // Get custom reading speed (default to 200 words per minute)
        $reading_speed = get_option( 'rte_custom_reading_speed', 200 );

        // Calculate reading time
        $reading_time = ceil( $word_count / $reading_speed );

        // Calculate letter count
        $letter_count = strlen( str_replace( ' ', '', wp_strip_all_tags( $post_content ) ) );

        // Calculate character count
        $character_count = strlen( wp_strip_all_tags( $post_content ) );

        // Calculate paragraph count
        $paragraphs = substr_count( $post_content, '</p>' );

        // Calculate sentence count (assuming that sentences end with ".", "!", or "?")
        $sentences = preg_match_all( '/[.!?]+/', $post_content, $matches );

        // Split the content into words
        $words = str_word_count( wp_strip_all_tags( $post_content ), 1 );

        // Calculate the total word length
        $total_word_length = array_sum( array_map( 'strlen', $words ) );

        // Calculate the average word length
        $average_word_length = $total_word_length / $word_count;

        // Count word frequencies
        $word_frequencies = array_count_values( $words );

        // Sort word frequencies in descending order
        arsort( $word_frequencies );

        // Take the top N most frequent words (e.g., top 5)
        $top_words = array_slice( $word_frequencies, 0, 5, true );

        // Add the counts and additional features to the post content
        $counts_html  = "<div class='wordcount-readingtime'>";
        $counts_html .= "<p>" . __( 'Word Count:', 'readtime-express' ) . " $word_count</p>";
        $counts_html .= "<p>" . __( 'Reading Time:', 'readtime-express' ) . " $reading_time " . __( 'minute(s)', 'readtime-express' ) . "</p>";
        $counts_html .= "<p>" . __( 'Letter Count:', 'readtime-express' ) . " $letter_count</p>";
        $counts_html .= "<p>" . __( 'Character Count:', 'readtime-express' ) . " $character_count</p>";
        $counts_html .= "<p>" . __( 'Paragraph Count:', 'readtime-express' ) . " $paragraphs</p>";
        $counts_html .= "<p>" . __( 'Sentence Count:', 'readtime-express' ) . " $sentences</p>";
        $counts_html .= "<p>" . __( 'Average Word Length:', 'readtime-express' ) . " " . number_format( $average_word_length, 2 ) . " " . __( 'characters', 'readtime-express' ) . "</p>";
        $counts_html .= "<p>" . __( 'Top 5 Most Frequent Words:', 'readtime-express' ) . "</p>";
        $counts_html .= "<ul>";
        foreach ( $top_words as $word => $count ) {
            $counts_html .= "<li>$word ($count " . __( 'occurrences', 'readtime-express' ) . ")</li>";
        }
        $counts_html .= "</ul>";
        $counts_html .= "</div>";

        $content .= $counts_html;
    }
    return $content;
}

add_filter( 'the_content', 'rte_add_wordcount_readingtime_to_content' );
