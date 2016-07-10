<?php

namespace CommerceToolsExporter\Response;

class ResponseUtil
{
    /**
     * @param array $response
     * @return null|string
     */
    public static function formatError(array $response)
    {
        if(!isset($response['errors']) || !is_array($response['errors'])) {
            return null;
        }

        return json_encode($response['errors']);
    }
}