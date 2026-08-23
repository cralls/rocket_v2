<?php
/**
 * Created by PhpStorm.
 * User: duongdiep
 * Date: 13/11/2018
 * Time: 13:24
 */

namespace MW\Affiliate\lib\internal\api\gmail;

class GmailGetContacts
{

    function get_request_token($oauth, $usePost = false, $useHmacSha1Sig = true, $passOAuthInHeader = false)
    {
        $retarr = [];  // return value
        $response = [];

        $url = 'https://www.google.com/accounts/OAuthGetRequestToken';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $oauth->oauth_consumer_key;
        $params['oauth_callback'] = $oauth->callback;
        $params['scope'] = 'https://www.google.com/m8/feeds';

        // compute signature and add it to the params list
        if ($useHmacSha1Sig) {

            $params['oauth_signature_method'] = 'HMAC-SHA1';
            $params['oauth_signature'] =
                $oauth->oauth_compute_hmac_sig(
                    $usePost ? 'POST' : 'GET',
                    $url,
                    $params,
                    $oauth->oauth_consumer_secret,
                    null
                );
        } else {
            print_r("signature mathod not support");
        }

        // Pass OAuth credentials in a separate header or in the query string
        if ($passOAuthInHeader) {

            $query_parameter_string = $oauth->oauth_http_build_query($params, false);

            $header = $oauth->build_oauth_header($params);

            $headers[] = $header;
        } else {
            $query_parameter_string = $oauth->oauth_http_build_query($params);
        }

        // POST or GET the request
        if ($usePost) {
            $request_url = $url;
            $oauth->logit("getreqtok:INFO:request_url:$request_url");
            $oauth->logit("getreqtok:INFO:post_body:$query_parameter_string");
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $response = do_post($request_url, $query_parameter_string, 443, $headers);
        } else {
            $request_url = $url . ($query_parameter_string ?
                    ('?' . $query_parameter_string) : '' );

            $oauth->logit("getreqtok:INFO:request_url:$request_url");

            $response = $oauth->do_get($request_url, 443, $headers);
        }

        // extract successful response
        if (!empty($response)) {
            list($info, $header, $body) = $response;
            $body_parsed = $oauth->oauth_parse_str($body);
            if (!empty($body_parsed)) {
                $oauth->logit("getreqtok:INFO:response_body_parsed:");
                //print_r($body_parsed);
            }
            $retarr = $response;
            $retarr[] = $body_parsed;
        }

        return $body_parsed;
    }

    function get_access_token($oauth, $request_token, $request_token_secret, $oauth_verifier, $usePost = false, $useHmacSha1Sig = true, $passOAuthInHeader = true)
    {
        $retarr = [];  // return value
        $response = [];

        $url = 'https://www.google.com/accounts/OAuthGetAccessToken';
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $oauth->oauth_consumer_key;
        $params['oauth_token'] = $request_token;
        $params['oauth_verifier'] = $oauth_verifier;

        // compute signature and add it to the params list
        if ($useHmacSha1Sig) {
            $params['oauth_signature_method'] = 'HMAC-SHA1';
            $params['oauth_signature'] =
                $oauth->oauth_compute_hmac_sig(
                    $usePost ? 'POST' : 'GET',
                    $url,
                    $params,
                    $oauth->oauth_consumer_secret,
                    $request_token_secret
                );
        } else {
            print_r("signature mathod not support");
        }
//
        if ($passOAuthInHeader) {
            $query_parameter_string = $oauth->oauth_http_build_query($params, false);
            $header = $oauth->build_oauth_header($params);
            $headers[] = $header;
        } else {
            $query_parameter_string = $oauth->oauth_http_build_query($params);
        }


        if ($usePost) {
            $request_url = $url;
            logit("getacctok:INFO:request_url:$request_url");
            logit("getacctok:INFO:post_body:$query_parameter_string");
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $response = $oauth->do_post($request_url, $query_parameter_string, 443, $headers);
        } else {
            $request_url = $url . ($query_parameter_string ?
                    ('?' . $query_parameter_string) : '' );
            /*                 print_r($headers);
                             echo "----2222222222-----";
             echo $request_url;
             echo "---";*/
            $oauth->logit("getacctok:INFO:request_url:$request_url");
            $response = $oauth->do_get($request_url, 443, $headers);
        }


        if (!empty($response)) {
            list($info, $header, $body) = $response;
            /* print_r($body);
             die();*/
            $body_parsed = $oauth->oauth_parse_str($body);
            if (!empty($body_parsed)) {
                $oauth->logit("getacctok:INFO:response_body_parsed:");
                //print_r($body_parsed);

            }
            $retarr = $response;
            $retarr[] = $body_parsed;
        }
        return $body_parsed;
    }


    function GetContacts($oauth, $access_token, $access_token_secret, $usePost = false, $passOAuthInHeader = true, $emails_count)
    {
        $retarr = [];  // return value
        $response = [];

        $url = "https://www.google.com/m8/feeds/contacts/default/full";
        $params['alt'] = 'json';
        $params['max-results'] = $emails_count;
        $params['oauth_version'] = '1.0';
        $params['oauth_nonce'] = mt_rand();
        $params['oauth_timestamp'] = time();
        $params['oauth_consumer_key'] = $oauth->oauth_consumer_key;
        $params['oauth_token'] = $access_token;

        // compute hmac-sha1 signature and add it to the params list
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_signature'] =
            $oauth->oauth_compute_hmac_sig(
                $usePost ? 'POST' : 'GET',
                $url,
                $params,
                $oauth->oauth_consumer_secret,
                $access_token_secret
            );

        // Pass OAuth credentials in a separate header or in the query string
        if ($passOAuthInHeader) {
            $query_parameter_string = $oauth->oauth_http_build_query($params, false);

            $header = $oauth->build_oauth_header($params);

            $headers[] = $header;
        } else {
            $query_parameter_string = $oauth->oauth_http_build_query($params);
        }

        // POST or GET the request
        if ($usePost) {
            $request_url = $url;
            $oauth->logit("callcontact:INFO:request_url:$request_url");
            $oauth->logit("callcontact:INFO:post_body:$query_parameter_string");
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $response = $oauth->do_post($request_url, $query_parameter_string, 80, $headers);

        } else {
            $request_url = $url . ($query_parameter_string ?
                    ('?' . $query_parameter_string) : '' );
            $oauth->logit("callcontact:INFO:request_url:$request_url");
            $response = $oauth->do_get($request_url, 443, $headers);
        }


        if (!empty($response)) {
            list($info, $header, $body) = $response;
            if ($body) {

                $oauth->logit("callcontact:INFO:response:");
                $contact = json_decode($oauth->json_pretty_print($body), true);

                //echo $contact['feed']['entry'][0]['gd$email'][0]['address'];
                return $contact['feed']['entry'];

            }
            $retarr = $response;
        }

        return $retarr;
    }
}
