<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodeExecutionController extends Controller
{
    /**
     * Supported languages mapping to Judge0 IDs.
     */
    protected $languages = [
        'javascript' => 63,
        'python' => 71,
        'cpp' => 54,
        'c' => 50,
        'java' => 62,
        'php' => 68,
    ];

    /**
     * Run the code via Judge0 API.
     */
    public function run(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string|in:' . implode(',', array_keys($this->languages)),
        ]);

        $code = $request->input('code');
        $language = $request->input('language');
        $languageId = $this->languages[$language];

        $apiUrl = config('services.judge0.url', env('JUDGE0_URL', 'https://judge0-ce.p.rapidapi.com'));
        $apiKey = config('services.judge0.key', env('JUDGE0_API_KEY'));

        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => parse_url($apiUrl, PHP_URL_HOST),
                'x-rapidapi-key' => $apiKey,
                'content-type' => 'application/json',
                'accept' => 'application/json',
            ])->post($apiUrl . '/submissions?base64_encoded=false&wait=true', [
                'source_code' => $code,
                'language_id' => $languageId,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Handle stdout, stderr, and compile_output
                $output = $result['stdout'] ?? '';
                $error = $result['stderr'] ?? '';
                $compileOutput = $result['compile_output'] ?? '';

                $finalOutput = $output;
                if ($compileOutput) {
                    $finalOutput .= ($finalOutput ? "\n" : "") . "Compile Output:\n" . $compileOutput;
                }
                if ($error) {
                    $finalOutput .= ($finalOutput ? "\n" : "") . "Error:\n" . $error;
                }

                if (!$finalOutput && isset($result['status']['description'])) {
                    $finalOutput = "Status: " . $result['status']['description'];
                }

                return response()->json([
                    'output' => $finalOutput ?: 'Program executed with no output.',
                    'status' => $result['status'] ?? null,
                ]);
            }

            Log::error('Judge0 API Error', ['response' => $response->body()]);
            return response()->json([
                'output' => 'Error: Failed to execute code. API returned status ' . $response->status(),
            ], 500);

        } catch (\Exception $e) {
            Log::error('Code Execution Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'output' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
