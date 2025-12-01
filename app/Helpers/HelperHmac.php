<?php

if (!function_exists('generateHmac')) {
                    function generateHmac($data_plain)
                    {
                                        $key = env('HMAC_SECRET_KEY');
                                        if (!$key) {
                                                            throw new \Exception('key tidak ada di env');
                                        }

                                        return hash_hmac('sha256', $data_plain, $key);
                    }
}
