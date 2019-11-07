<?php

namespace App\NodCredit\Docparser;

class NodcreditDocparser extends \Docparser\Docparser
{

    /**
     * @param $parserId
     * @param $documentId
     * @param string $format
     * @param bool $includeChildren
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface|string
     */
    public function getResultsByDocument($parserId, $documentId, $format = 'object', $includeChildren = false)
    {
        $request = $this->createRequest();
        $endpoint = 'results/' . $parserId . '/' . $documentId;

        $response = $request->makeGetRequest($endpoint, [
            'format' => $format,
            'include_children' => $includeChildren
        ]);

        return $response;
    }


}