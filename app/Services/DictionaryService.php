<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class DictionaryService
{
    protected string $baseUrl = 'https://api.dictionaryapi.dev/api/v2/entries';

    /**
     * Search for a word definition.
     */
    public function searchWord(string $word, string $language = 'en'): array
    {
        $cacheKey = "dictionary_{$language}_{$word}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$language}/{$word}");

            if ($response->successful()) {
                $data = $response->json();

                // Cache the result for 24 hours
                Cache::put($cacheKey, $data, now()->addHours(24));

                return $data;
            }

            // Handle different error responses
            if ($response->status() === 404) {
                return [
                    'error' => 'Word not found',
                    'message' => "The word '{$word}' was not found in the dictionary.",
                    'status' => 404
                ];
            }

            return [
                'error' => 'API Error',
                'message' => 'Failed to fetch word definition',
                'status' => $response->status()
            ];

        } catch (\Exception $e) {
            \Log::error('Dictionary API error', [
                'word' => $word,
                'language' => $language,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Service Unavailable',
                'message' => 'Dictionary service is temporarily unavailable',
                'status' => 503
            ];
        }
    }

    /**
     * Get word pronunciation.
     */
    public function getPronunciation(array $wordData): ?string
    {
        if (empty($wordData) || isset($wordData['error'])) {
            return null;
        }

        $firstWord = $wordData[0] ?? null;
        if (!$firstWord) {
            return null;
        }

        // Try to get pronunciation from phonetics
        $phonetics = $firstWord['phonetics'] ?? [];
        foreach ($phonetics as $phonetic) {
            if (isset($phonetic['audio']) && !empty($phonetic['audio'])) {
                return $phonetic['audio'];
            }
        }

        return null;
    }

    /**
     * Get word definitions.
     */
    public function getDefinitions(array $wordData): array
    {
        if (empty($wordData) || isset($wordData['error'])) {
            return [];
        }

        $definitions = [];
        $firstWord = $wordData[0] ?? null;

        if (!$firstWord) {
            return [];
        }

        $meanings = $firstWord['meanings'] ?? [];
        foreach ($meanings as $meaning) {
            $partOfSpeech = $meaning['partOfSpeech'] ?? 'unknown';
            $defs = $meaning['definitions'] ?? [];

            foreach ($defs as $def) {
                $definitions[] = [
                    'definition' => $def['definition'] ?? '',
                    'part_of_speech' => $partOfSpeech,
                    'example' => $def['example'] ?? null,
                ];
            }
        }

        return $definitions;
    }

    /**
     * Get word synonyms.
     */
    public function getSynonyms(array $wordData): array
    {
        if (empty($wordData) || isset($wordData['error'])) {
            return [];
        }

        $synonyms = [];
        $firstWord = $wordData[0] ?? null;

        if (!$firstWord) {
            return [];
        }

        $meanings = $firstWord['meanings'] ?? [];
        foreach ($meanings as $meaning) {
            $syns = $meaning['synonyms'] ?? [];
            $synonyms = array_merge($synonyms, $syns);
        }

        return array_unique($synonyms);
    }

    /**
     * Get word antonyms.
     */
    public function getAntonyms(array $wordData): array
    {
        if (empty($wordData) || isset($wordData['error'])) {
            return [];
        }

        $antonyms = [];
        $firstWord = $wordData[0] ?? null;

        if (!$firstWord) {
            return [];
        }

        $meanings = $firstWord['meanings'] ?? [];
        foreach ($meanings as $meaning) {
            $ants = $meaning['antonyms'] ?? [];
            $antonyms = array_merge($antonyms, $ants);
        }

        return array_unique($antonyms);
    }

    /**
     * Get word examples.
     */
    public function getExamples(array $wordData): array
    {
        if (empty($wordData) || isset($wordData['error'])) {
            return [];
        }

        $examples = [];
        $firstWord = $wordData[0] ?? null;

        if (!$firstWord) {
            return [];
        }

        $meanings = $firstWord['meanings'] ?? [];
        foreach ($meanings as $meaning) {
            $defs = $meaning['definitions'] ?? [];
            foreach ($defs as $def) {
                if (isset($def['example']) && !empty($def['example'])) {
                    $examples[] = [
                        'example' => $def['example'],
                        'part_of_speech' => $meaning['partOfSpeech'] ?? 'unknown',
                    ];
                }
            }
        }

        return $examples;
    }

    /**
     * Get comprehensive word information.
     */
    public function getWordInfo(string $word, string $language = 'en'): array
    {
        $wordData = $this->searchWord($word, $language);

        if (isset($wordData['error'])) {
            return $wordData;
        }

        return [
            'word' => $word,
            'language' => $language,
            'pronunciation' => $this->getPronunciation($wordData),
            'definitions' => $this->getDefinitions($wordData),
            'synonyms' => $this->getSynonyms($wordData),
            'antonyms' => $this->getAntonyms($wordData),
            'examples' => $this->getExamples($wordData),
            'raw_data' => $wordData, // Keep original data for reference
        ];
    }

    /**
     * Clear cache for a specific word.
     */
    public function clearCache(string $word, string $language = 'en'): void
    {
        $cacheKey = "dictionary_{$language}_{$word}";
        Cache::forget($cacheKey);
    }

    /**
     * Clear all dictionary cache.
     */
    public function clearAllCache(): void
    {
        // This is a simple implementation
        // In production, you might want to use cache tags for better management
        Cache::flush();
    }

    public function search(string $word): array
    {
        $response = Http::get("https://api.dictionaryapi.dev/api/v2/entries/en/" . urlencode($word));
        if (!$response->ok()) {
            return [
                'success' => false,
                'message' => 'Word not found or API error.',
                'data' => null,
            ];
        }
        $data = $response->json();
        if (!is_array($data) || empty($data)) {
            return [
                'success' => false,
                'message' => 'No data found.',
                'data' => null,
            ];
        }
        $entry = $data[0];
        $result = [
            'word' => $entry['word'] ?? '',
            'phonetics' => $entry['phonetics'] ?? [],
            'meanings' => [],
        ];
        foreach ($entry['meanings'] ?? [] as $meaning) {
            $definitions = [];
            foreach ($meaning['definitions'] ?? [] as $def) {
                $definitions[] = [
                    'definition' => $def['definition'] ?? '',
                    'example' => $def['example'] ?? null,
                    'synonyms' => $def['synonyms'] ?? [],
                ];
            }
            $result['meanings'][] = [
                'partOfSpeech' => $meaning['partOfSpeech'] ?? '',
                'definitions' => $definitions,
            ];
        }
        return [
            'success' => true,
            'data' => $result,
        ];
    }
}
