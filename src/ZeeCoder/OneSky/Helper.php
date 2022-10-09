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
}
