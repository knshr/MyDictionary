<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DictionaryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Dictionary
 *
 * APIs for searching English words using the Free Dictionary API
 */
class DictionaryController extends Controller
{
    public function __construct(
        private DictionaryService $dictionaryService
    ) {}

    /**
     * Search Word
     *
     * Search for a word definition using the Free Dictionary API.
     *
     * @urlParam word string required The word to search for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "word": "hello",
     *     "language": "en",
     *     "pronunciation": "https://api.dictionaryapi.dev/media/pronunciations/en/hello-au.mp3",
     *     "definitions": [
     *       {
     *         "definition": "A greeting or an expression of goodwill.",
     *         "part_of_speech": "noun",
     *         "example": "Hello, how are you today?"
     *       }
     *     ],
     *     "synonyms": ["hi", "hey", "greetings"],
     *     "antonyms": ["goodbye", "farewell"],
     *     "examples": [
     *       {
     *         "example": "Hello, how are you today?",
     *         "part_of_speech": "noun"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Word not found",
     *   "data": {
     *     "error": "Word not found",
     *     "message": "The word 'nonexistentword' was not found in the dictionary.",
     *     "status": 404
     *   }
     * }
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string',
        ]);
        $word = $request->input('q');
        $result = app(DictionaryService::class)->search($word);
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $result['data'],
        ]);
    }

    /**
     * Get Word Definitions
     *
     * Get only the definitions for a word.
     *
     * @urlParam word string required The word to get definitions for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "definition": "A greeting or an expression of goodwill.",
     *       "part_of_speech": "noun",
     *       "example": "Hello, how are you today?"
     *     }
     *   ]
     * }
     */
    public function definitions(Request $request, string $word): JsonResponse
    {
        $language = $request->query('language', 'en');

        try {
            $wordData = $this->dictionaryService->searchWord($word, $language);

            if (isset($wordData['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $wordData['error'],
                    'data' => $wordData
                ], $wordData['status'] ?? 400);
            }

            $definitions = $this->dictionaryService->getDefinitions($wordData);

            return response()->json([
                'success' => true,
                'data' => $definitions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get definitions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Word Synonyms
     *
     * Get synonyms for a word.
     *
     * @urlParam word string required The word to get synonyms for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["hi", "hey", "greetings"]
     * }
     */
    public function synonyms(Request $request, string $word): JsonResponse
    {
        $language = $request->query('language', 'en');

        try {
            $wordData = $this->dictionaryService->searchWord($word, $language);

            if (isset($wordData['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $wordData['error'],
                    'data' => $wordData
                ], $wordData['status'] ?? 400);
            }

            $synonyms = $this->dictionaryService->getSynonyms($wordData);

            return response()->json([
                'success' => true,
                'data' => $synonyms
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get synonyms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Word Antonyms
     *
     * Get antonyms for a word.
     *
     * @urlParam word string required The word to get antonyms for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["goodbye", "farewell"]
     * }
     */
    public function antonyms(Request $request, string $word): JsonResponse
    {
        $language = $request->query('language', 'en');

        try {
            $wordData = $this->dictionaryService->searchWord($word, $language);

            if (isset($wordData['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $wordData['error'],
                    'data' => $wordData
                ], $wordData['status'] ?? 400);
            }

            $antonyms = $this->dictionaryService->getAntonyms($wordData);

            return response()->json([
                'success' => true,
                'data' => $antonyms
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get antonyms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Word Examples
     *
     * Get example sentences for a word.
     *
     * @urlParam word string required The word to get examples for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "example": "Hello, how are you today?",
     *       "part_of_speech": "noun"
     *     }
     *   ]
     * }
     */
    public function examples(Request $request, string $word): JsonResponse
    {
        $language = $request->query('language', 'en');

        try {
            $wordData = $this->dictionaryService->searchWord($word, $language);

            if (isset($wordData['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $wordData['error'],
                    'data' => $wordData
                ], $wordData['status'] ?? 400);
            }

            $examples = $this->dictionaryService->getExamples($wordData);

            return response()->json([
                'success' => true,
                'data' => $examples
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get examples',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Word Pronunciation
     *
     * Get pronunciation audio URL for a word.
     *
     * @urlParam word string required The word to get pronunciation for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "data": "https://api.dictionaryapi.dev/media/pronunciations/en/hello-au.mp3"
     * }
     */
    public function pronunciation(Request $request, string $word): JsonResponse
    {
        $language = $request->query('language', 'en');

        try {
            $wordData = $this->dictionaryService->searchWord($word, $language);

            if (isset($wordData['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $wordData['error'],
                    'data' => $wordData
                ], $wordData['status'] ?? 400);
            }

            $pronunciation = $this->dictionaryService->getPronunciation($wordData);

            return response()->json([
                'success' => true,
                'data' => $pronunciation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pronunciation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear Cache
     *
     * Clear the dictionary cache for a specific word.
     *
     * @authenticated
     * @urlParam word string required The word to clear cache for. Example: hello
     * @queryParam language string The language code (default: en). Example: en
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Cache cleared successfully"
     * }
     */
    public function clearCache(Request $request, string $word): JsonResponse
    {
        $language = $request->query('language', 'en');

        try {
            $this->dictionaryService->clearCache($word, $language);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
