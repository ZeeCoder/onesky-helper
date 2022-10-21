<?php

namespace ZeeCoder\OneSky;

use Onesky\Api\Client;

/**
 * A wrapper class around the OneSky PHP Api Client SDK.
 */
class Helper
{
    public $client = null;
    public $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;

		if ($config['api_key'] && $config['api_secret']) {
	        $this->client =
	            (new Client())
	            ->setApiKey($config['api_key'])
	            ->setSecret($config['api_secret'])
	        ;
		}
    }

	public static function withConfig($key, $secret, $project)
	{
		return new self([
			'api_key' => $key,
			'api_secret' => $secret,
			'project_id' => $project,
		]);
	}

    /**
     * Gets the 'projects' resource's 'languages' action.
     */
    public function getProjectLanguages()
    {
        return json_decode(
            $this->client->projects(
                'languages',
                [
                    'project_id' => $this->config['project_id'],
                ]
            )
        );
    }

    /**
     * Gets only a list of the project's locale codes in an array
     */
    public function getProjectLocaleCodes()
    {
        $projectLanguagesResponse = $this->getProjectLanguages();

        return array_map(function($dataObject) {
            return $dataObject->code;
        }, $projectLanguagesResponse->data);
    }

    /**
     * Getting a list of the translation files
     * (This will only list 100 files at max.)
     */
    public function getProjectTranslationFiles()
    {
        return json_decode(
            $this->client->files(
                'list',
                [
                    'project_id' => $this->config['project_id'],
                    'per_page' => 100,
                ]
            )
        );
    }

    /**
     * Getting only a list of the project translation filenames
     */
    public function getProjectTranslationFileNames()
    {
        $projectFilesResponse = $this->getProjectTranslationFiles();

        return array_map(function($dataObject) {
            return $dataObject->file_name;
        }, $projectFilesResponse->data);
    }

    /**
     * Getting a translation file by the filename and locale
     */
    public function getTranslationFile($filename, $locale)
    {
        return $this->client->translations(
            'export',
            [
                'project_id' => $this->config['project_id'],
                'locale' => $locale,
                'source_file_name' => $filename
            ]
        );
    }

	/**
	 * Sends a file for further processing
	 * @param string $filePath
	 * @param ?string $locale A locale in the format pt-BR, or null if this is the base language file
	 * @param string $format Any of the formats defined by OneSky (see docs below). If not given, it's inferred from the extension - or raises an extension if that's not possible. Pay attention: some extensions share different formats supported by OneSky, such as .php holding PHP, PHP_SHORT_ARRAY or PHP_VARIABLES. Refer to OneSky docs to make sure you're using the right format for your use-case.
	 * @param bool $keepStrings If false, strings missing from the same filename previously uploaded get deprecated.
	 * @param bool $allowSameAsOriginal If false, skips importing translations with the same content as their key.
	 * @see https://github.com/onesky/api-documentation-platform/blob/master/reference/format.md
	 * @see https://github.com/onesky/api-documentation-platform/blob/master/resources/file.md#upload---upload-a-file
	 * @return mixed
	 */
	public function uploadFile($filePath, $locale = null, $format = null, $keepStrings = true, $allowSameAsOriginal = false)
	{
		if (!$format) {
			$ext = strtoupper(substr($filePath, strrpos($filePath, '.') + 1));
			switch ($ext) {
				case 'PO':
					$format = 'GNU_PO';
					break;
				case 'POT':
					$format = 'GNU_POT';
					break;
				case 'JSON':
					$format = 'HIERARCHICAL_JSON';
					break;
				case 'PROPERTIES':
					$format = 'JAVA_PROPERTIES';
					break;
				case 'HTML':
				case 'INI':
				case 'PHP':
				case 'RESJSON':
				case 'RESW':
				case 'RRC':
				case 'TMX':
				case 'XLIFF':
				case 'YML':
				case 'YAML':
					$format = $ext;
					break;
			}
			if (!$format) {
				throw new \InvalidArgumentException("Cannot infer file format for $filePath");
			}
		}

		return $this->client->files(
			'upload',
			[
				'project_id' => $this->config['project_id'],
				'file' => $filePath,
				'file_format' => $format,
				'locale' => $locale,
				'is_keeping_all_strings' => $keepStrings,
				'is_allow_translation_same_as_original' => $allowSameAsOriginal
			]
		);
	}
}
