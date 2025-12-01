<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

if (!function_exists('terbilang')) {
    function terbilang($x)
    {
        $angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        if ($x < 12)
            return " " . $angka[$x];
        elseif ($x < 20)
            return terbilang($x - 10) . " belas";
        elseif ($x < 100)
            return terbilang($x / 10) . " puluh" . terbilang($x % 10);
        elseif ($x < 200)
            return "seratus" . terbilang($x - 100);
        elseif ($x < 1000)
            return terbilang($x / 100) . " ratus" . terbilang($x % 100);
        elseif ($x < 2000)
            return "seribu" . terbilang($x - 1000);
        elseif ($x < 1000000)
            return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
        elseif ($x < 1000000000)
            return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
        elseif ($x < 1000000000000)
            return terbilang($x / 1000000000) . " milyar" . terbilang(fmod($x, 1000000000));
        elseif ($x < 1000000000000000)
            return terbilang($x / 1000000000000) . " triliun" . terbilang(fmod($x, 1000000000000));
        return "terlalu besar";
    }
}

if (!function_exists('active_class')) {
    function active_class($paths, $activeClass = 'active')
    {
        foreach ((array) $paths as $path) {
            if (Request::is($path)) {
                return $activeClass;
            }
        }

        return '';
    }
}

if (!function_exists('has_access')) {
    function has_access($fitur)
    {
        $user = Auth::user();

        if (!$user) return false;

        return $user->Role()
            ->where('fitur', $fitur)
            ->where('akses', 1)
            ->exists();
    }
}
function seal($var)
{
    $certFile = config('services.bssn.cert_path');
    $keyFile = config('services.bssn.key_path');
    $url = 'https://10.202.26.39:2709/seal';

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);

    // Kirimkan langsung plaintext-nya, tanpa JSON
    curl_setopt($ch, CURLOPT_POSTFIELDS, $var);

    // Jangan pakai application/json jika kirim plaintext
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: text/plain'
    ));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($response, true); // Jika response memang JSON
}
function unseal(array $var)
{
    $certFile = config('services.bssn.cert_path');
    $keyFile = config('services.bssn.key_path');
    $url = 'https://10.202.26.39:2709/unseal';

    $postData = json_encode([
        'Ciphertext' => array_map(function ($name) {
            return ['text' => $name];
        }, $var)
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));

    $response = curl_exec($ch);
    curl_close($ch);
    $parsedResponse = json_decode($response, true);
    $decryptedNames = $parsedResponse['Plaintext'] ?? [];

    // Mengembalikan array asosiatif untuk memudahkan pencocokan
    $result = [];
    foreach ($decryptedNames as $index => $item) {
        $result[$var[$index]] = $item['text'];
    }

    return $result;
}


if (!function_exists('sealNames')) {
    /**
     * Melakukan batch enkripsi (seal) pada array teks.
     *
     * @param array $plainTexts
     * @return array
     */
    function sealNames(array $plainTexts)
    {
        $certFile = config('services.bssn.cert_path');
        $keyFile = config('services.bssn.key_path');
        $url = 'https://10.202.26.39:2709/seal';

        $postData = json_encode([
            'Plaintext' => array_map(function ($text) {
                return ['text' => $text];
            }, $plainTexts)
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
        curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $parsedResponse = json_decode($response, true);
        $encryptedTexts = $parsedResponse['Ciphertext'] ?? [];

        $result = [];
        foreach ($encryptedTexts as $index => $item) {
            $result[$plainTexts[$index]] = $item['text'];
        }

        return $result;
    }
}

if (!function_exists('unsealNames')) {
    /**
     * Melakukan batch dekripsi (unseal) pada array ciphertext.
     *
     * @param array $encryptedTexts
     * @return array
     */
    function unsealNames(array $encryptedTexts)
    {
        $certFile = config('services.bssn.cert_path');
        $keyFile = config('services.bssn.key_path');
        $url = 'https://10.202.26.39:2709/unseal';

        $postData = json_encode([
            'Ciphertext' => array_map(function ($text) {
                return ['text' => $text];
            }, $encryptedTexts)
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
        curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $parsedResponse = json_decode($response, true);
        $decryptedTexts = $parsedResponse['Plaintext'] ?? [];

        $result = [];
        foreach ($decryptedTexts as $index => $item) {
            $result[$encryptedTexts[$index]] = $item['text'];
        }

        return $result;
    }
}



if (!function_exists('hmac')) {
    /**
     * Melakukan HMAC pada sebuah teks tunggal.
     *
     * @param string $plainText
     * @return string|null
     */
    function hmac($plainText)
    {
        $certFile = config('services.bssn.cert_path');
        $keyFile = config('services.bssn.key_path');
        // Gunakan URL yang diberikan pengguna
        $url = 'https://10.202.26.39:2709/hmac';
        //$url = '[https://10.202.26.39:2709/hmac](https://10.202.26.39:2709/hmac)';

        // Perbaikan: Ubah payload agar sesuai dengan format JSON yang Anda berikan
        $postData = json_encode([
            'Message' => $plainText
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
        curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        // --- HANYA UNTUK TESTING ---
        // Baris ini akan menghentikan eksekusi dan menampilkan hasil respons mentah.
        // Hapus baris ini setelah pengujian selesai.
        //dd($response);
        // -------------------------

        $parsedResponse = json_decode($response, true);

        // Asumsi key response untuk single item adalah 'HMACstring'
        return $parsedResponse['Value'] ?? null;
    }
}

if (!function_exists('verifyhmac')) {
    /**
     * Memverifikasi HMAC pada sebuah teks tunggal.
     *
     * @param string $plainText Teks asli yang akan diverifikasi.
     * @param string $hstring String HMAC yang akan dibandingkan.
     * @return bool|null Mengembalikan true jika valid, false jika tidak valid, atau null jika ada error.
     */
    function verifyhmac(string $plainText, string $hstring)
    {
        $certFile = config('services.bssn.cert_path');
        $keyFile = config('services.bssn.key_path');
        // Gunakan URL yang diberikan pengguna
        $url = 'https://10.202.26.39:2709/verifyhmac';

        // Payload diubah agar sesuai dengan format yang dibutuhkan oleh API
        $postData = json_encode([
            'Message' => $plainText,
            'HMACstring' => $hstring
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT, $certFile);
        curl_setopt($ch, CURLOPT_SSLKEY, $keyFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $parsedResponse = json_decode($response, true);

        // Asumsi respons API adalah {"Value": true} atau {"Value": false}
        // Mengembalikan nilai boolean dari kunci 'Value'.
        return $parsedResponse['Value'] ?? null;
    }
}
if (!function_exists('token_sadarkajabar')) {
    /**
     * Mendapatkan token SPLP untuk API Sadarkajabar.
     *
     * @param string $username
     * @param string $password
     * @param string $urlTokenSadarkajabar
     * @param string $apikey
     * @return string|null
     */
    function token_sadarkajabar()
    {
        $username = "4000000000001012";
        $password = "(FW:kyryC]iz2ku(vI";
        $apikey = "eyJ4NXQjUzI1NiI6Ik16WXhNbUZrT0dZd01XSTBaV05tTkRjeE5HWXdZbU00WlRBM01XSTJOREF6WkdRek5HTTBaR1JsTmpKa09ERmtaRFJpT1RGa01XRmhNelUyWkdWbE5nPT0iLCJraWQiOiJnYXRld2F5X2NlcnRpZmljYXRlX2FsaWFzIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYifQ==.eyJzdWIiOiJwZF9kaXNwZXJraW0xQGphYmFycHJvdi5nby5pZCIsImFwcGxpY2F0aW9uIjp7Im93bmVyIjoicGRfZGlzcGVya2ltMUBqYWJhcnByb3YuZ28uaWQiLCJ0aWVyUXVvdGFUeXBlIjpudWxsLCJ0aWVyIjoiVW5saW1pdGVkIiwibmFtZSI6IlNJUEFUIiwiaWQiOjIzMjEsInV1aWQiOiI0ZWEwZWFmOC0xNWQ5LTQ4MTgtYWMwMy0zYTVlMjc3NGQ3MDgifSwiaXNzIjoiaHR0cHM6XC9cL3NwbHAubGF5YW5hbi5nby5pZDo0NDNcL29hdXRoMlwvdG9rZW4iLCJ0aWVySW5mbyI6eyJCcm9uemUiOnsidGllclF1b3RhVHlwZSI6InJlcXVlc3RDb3VudCIsImdyYXBoUUxNYXhDb21wbGV4aXR5IjowLCJncmFwaFFMTWF4RGVwdGgiOjAsInN0b3BPblF1b3RhUmVhY2giOnRydWUsInNwaWtlQXJyZXN0TGltaXQiOjAsInNwaWtlQXJyZXN0VW5pdCI6bnVsbH0sIlVubGltaXRlZCI6eyJ0aWVyUXVvdGFUeXBlIjoicmVxdWVzdENvdW50IiwiZ3JhcGhRTE1heENvbXBsZXhpdHkiOjAsImdyYXBoUUxNYXhEZXB0aCI6MCwic3RvcE9uUXVvdGFSZWFjaCI6dHJ1ZSwic3Bpa2VBcnJlc3RMaW1pdCI6MCwic3Bpa2VBcnJlc3RVbml0IjpudWxsfX0sImtleXR5cGUiOiJQUk9EVUNUSU9OIiwicGVybWl0dGVkUmVmZXJlciI6IiIsInN1YnNjcmliZWRBUElzIjpbeyJzdWJzY3JpYmVyVGVuYW50RG9tYWluIjoiamFiYXJwcm92LmdvLmlkIiwibmFtZSI6IlNJUEFORFUiLCJjb250ZXh0IjoiXC90XC9qYWJhcnByb3YuZ28uaWRcL3NpcGFuZHVcLzEiLCJwdWJsaXNoZXIiOiJhZG1pbkBqYWJhcnByb3YuZ28uaWQiLCJ2ZXJzaW9uIjoiMSIsInN1YnNjcmlwdGlvblRpZXIiOiJVbmxpbWl0ZWQifSx7InN1YnNjcmliZXJUZW5hbnREb21haW4iOiJqYWJhcnByb3YuZ28uaWQiLCJuYW1lIjoiU0lERUJBUiIsImNvbnRleHQiOiJcL3RcL2phYmFycHJvdi5nby5pZFwvc2lkZWJhclwvMSIsInB1Ymxpc2hlciI6ImFkbWluQGphYmFycHJvdi5nby5pZCIsInZlcnNpb24iOiIxIiwic3Vic2NyaXB0aW9uVGllciI6IlVubGltaXRlZCJ9LHsic3Vic2NyaWJlclRlbmFudERvbWFpbiI6ImphYmFycHJvdi5nby5pZCIsIm5hbWUiOiJTQURBUktBSkFCQVIiLCJjb250ZXh0IjoiXC90XC9qYWJhcnByb3YuZ28uaWRcL3NhZGFya2FqYWJhclwvMS4wMCIsInB1Ymxpc2hlciI6ImRpc2tvbV9lZGR5QGphYmFycHJvdi5nby5pZCIsInZlcnNpb24iOiIxLjAwIiwic3Vic2NyaXB0aW9uVGllciI6IkJyb256ZSJ9XSwidG9rZW5fdHlwZSI6ImFwaUtleSIsInBlcm1pdHRlZElQIjoiIiwiaWF0IjoxNzQ4OTEzNjk2LCJqdGkiOiI5ZTVhMjA4Ny0zZTA2LTQ2ZGQtOTBiOC0xYjA1ZmM1ODMyOWUifQ==.NdrKLEvnnOeSKhcOdGYODSzIcZ4-9UXhm5xVE_cpuBgorMsCngB4LN2I7tyeNhD2zygKCgCZR81qeJo3dul9N068K9qltILs2kmjUe7edQpvO7eWLvvoH9T-8pfyZRHazjyCD9XdJa-szo0Q8UuvPwrhWGIl6C5TX3W-JKHIsRaKKfC9uDRPI6-bLx93QBYa_cbFxxegoTO6IdAiaEZS3cIQDuSZA1adEvNt7VhDFziT9fOlTY2nX933Oyr7d9PEC6GFYcq-gBX6pdA50rGa3kNexDy05LVs1WWUQd628w3sDLS60bT7hE-z7a5YBPRvwbf5j8MKVbA0PAd6RAJCSA==";
        $urlTokenSadarkajabar = 'https://api-splp.layanan.go.id/t/jabarprov.go.id/sadarkajabar/1.00/auth/getToken';
        $postDataKirim = json_encode(
            array(
                "username" => $username,
                "password" => $password,
            )
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $urlTokenSadarkajabar);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataKirim);

        $headers = array();
        $headers[] = 'Content-Type:application/json';
        $headers[] = 'apikey:' . $apikey;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL Error getting token (token_sadarkajabar): ' . curl_error($ch));
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        $result1 = json_decode($result);
        return $result1->access_token ?? null;
    }
}
if (!function_exists('getDataIndividu')) {
    /**
     * Memanggil API getDataIndividu dari SPLP untuk mendapatkan data individu berdasarkan NIK.
     *
     * @param string $nik
     * @return array|null
     */
    function getDataIndividu($nik)
    {
        $username = "4000000000001012";
        $password = "(FW:kyryC]iz2ku(vI";
        $apikey = "eyJ4NXQjUzI1NiI6Ik16WXhNbUZrT0dZd01XSTBaV05tTkRjeE5HWXdZbU00WlRBM01XSTJOREF6WkdRek5HTTBaR1JsTmpKa09ERmtaRFJpT1RGa01XRmhNelUyWkdWbE5nPT0iLCJraWQiOiJnYXRld2F5X2NlcnRpZmljYXRlX2FsaWFzIiwidHlwIjoiSldUIiwiYWxnIjoiUlMyNTYifQ==.eyJzdWIiOiJwZF9kaXNwZXJraW0xQGphYmFycHJvdi5nby5pZCIsImFwcGxpY2F0aW9uIjp7Im93bmVyIjoicGRfZGlzcGVya2ltMUBqYWJhcnByb3YuZ28uaWQiLCJ0aWVyUXVvdGFUeXBlIjpudWxsLCJ0aWVyIjoiVW5saW1pdGVkIiwibmFtZSI6IlNJUEFUIiwiaWQiOjIzMjEsInV1aWQiOiI0ZWEwZWFmOC0xNWQ5LTQ4MTgtYWMwMy0zYTVlMjc3NGQ3MDgifSwiaXNzIjoiaHR0cHM6XC9cL3NwbHAubGF5YW5hbi5nby5pZDo0NDNcL29hdXRoMlwvdG9rZW4iLCJ0aWVySW5mbyI6eyJCcm9uemUiOnsidGllclF1b3RhVHlwZSI6InJlcXVlc3RDb3VudCIsImdyYXBoUUxNYXhDb21wbGV4aXR5IjowLCJncmFwaFFMTWF4RGVwdGgiOjAsInN0b3BPblF1b3RhUmVhY2giOnRydWUsInNwaWtlQXJyZXN0TGltaXQiOjAsInNwaWtlQXJyZXN0VW5pdCI6bnVsbH0sIlVubGltaXRlZCI6eyJ0aWVyUXVvdGFUeXBlIjoicmVxdWVzdENvdW50IiwiZ3JhcGhRTE1heENvbXBsZXhpdHkiOjAsImdyYXBoUUxNYXhEZXB0aCI6MCwic3RvcE9uUXVvdGFSZWFjaCI6dHJ1ZSwic3Bpa2VBcnJlc3RMaW1pdCI6MCwic3Bpa2VBcnJlc3RVbml0IjpudWxsfX0sImtleXR5cGUiOiJQUk9EVUNUSU9OIiwicGVybWl0dGVkUmVmZXJlciI6IiIsInN1YnNjcmliZWRBUElzIjpbeyJzdWJzY3JpYmVyVGVuYW50RG9tYWluIjoiamFiYXJwcm92LmdvLmlkIiwibmFtZSI6IlNJUEFORFUiLCJjb250ZXh0IjoiXC90XC9qYWJhcnByb3YuZ28uaWRcL3NpcGFuZHVcLzEiLCJwdWJsaXNoZXIiOiJhZG1pbkBqYWJhcnByb3YuZ28uaWQiLCJ2ZXJzaW9uIjoiMSIsInN1YnNjcmlwdGlvblRpZXIiOiJVbmxpbWl0ZWQifSx7InN1YnNjcmliZXJUZW5hbnREb21haW4iOiJqYWJhcnByb3YuZ28uaWQiLCJuYW1lIjoiU0lERUJBUiIsImNvbnRleHQiOiJcL3RcL2phYmFycHJvdi5nby5pZFwvc2lkZWJhclwvMSIsInB1Ymxpc2hlciI6ImFkbWluQGphYmFycHJvdi5nby5pZCIsInZlcnNpb24iOiIxIiwic3Vic2NyaXB0aW9uVGllciI6IlVubGltaXRlZCJ9LHsic3Vic2NyaWJlclRlbmFudERvbWFpbiI6ImphYmFycHJvdi5nby5pZCIsIm5hbWUiOiJTQURBUktBSkFCQVIiLCJjb250ZXh0IjoiXC90XC9qYWJhcnByb3YuZ28uaWRcL3NhZGFya2FqYWJhclwvMS4wMCIsInB1Ymxpc2hlciI6ImRpc2tvbV9lZGR5QGphYmFycHJvdi5nby5pZCIsInZlcnNpb24iOiIxLjAwIiwic3Vic2NyaXB0aW9uVGllciI6IkJyb256ZSJ9XSwidG9rZW5fdHlwZSI6ImFwaUtleSIsInBlcm1pdHRlZElQIjoiIiwiaWF0IjoxNzQ4OTEzNjk2LCJqdGkiOiI5ZTVhMjA4Ny0zZTA2LTQ2ZGQtOTBiOC0xYjA1ZmM1ODMyOWUifQ==.NdrKLEvnnOeSKhcOdGYODSzIcZ4-9UXhm5xVE_cpuBgorMsCngB4LN2I7tyeNhD2zygKCgCZR81qeJo3dul9N068K9qltILs2kmjUe7edQpvO7eWLvvoH9T-8pfyZRHazjyCD9XdJa-szo0Q8UuvPwrhWGIl6C5TX3W-JKHIsRaKKfC9uDRPI6-bLx93QBYa_cbFxxegoTO6IdAiaEZS3cIQDuSZA1adEvNt7VhDFziT9fOlTY2nX933Oyr7d9PEC6GFYcq-gBX6pdA50rGa3kNexDy05LVs1WWUQd628w3sDLS60bT7hE-z7a5YBPRvwbf5j8MKVbA0PAd6RAJCSA==";

        // url get Token SPLP
        $urlTokenSadarkajabar = 'https://api-splp.layanan.go.id/t/jabarprov.go.id/sadarkajabar/1.00/auth/getToken';
        // baseUrl API
        $baseURL = 'https://api-splp.layanan.go.id/t/jabarprov.go.id';
        $token = "Bearer " . token_sadarkajabar();
        $headers      = [
            'Content-Type: application/json',
            'Authorization: ' . $token,
            'apikey:' . $apikey
        ];
        $postDataKirim = json_encode(
            array(
                "page" => 1,
                "perpage" => 10,
                "kab_kota" => "",
                "kecamatan" => "",
                "kelurahan_desa" => "",
                "id_pertanyaan" => "26, 29, 30, 31, 36", //26 nama 29 jenis kelamin 30 tanggal lahir 31 status pernikahan 36 agama
                "nik" => $nik
            )
        );
        $pathResource = '/sadarkajabar/1.00/api/integrasi/getDataIndividu';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $baseURL . $pathResource,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_POSTFIELDS => $postDataKirim,
            CURLOPT_HTTPHEADER => $headers
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $responseData = json_decode($response, true);

        // --- Mulai Pemetaan Data ---
        if (isset($responseData['data']['data'][0])) {
            $individuData = $responseData['data']['data'][0];
            $questionsAnswers = $individuData['data'] ?? []; // Array pertanyaan dan jawaban

            $mappedData = [
                'nama' => '',
                'tgl_lahir' => '',
                'no_tlp' => '', // Tidak ada di respons ini, akan tetap kosong
                'jenis_kelamin' => '',
                'status_kawin' => '',
                'agama' => '',
                'email' => '' // Tidak ada di respons ini, akan tetap kosong
            ];

            // Mapping ID pertanyaan ke nama field form
            foreach ($questionsAnswers as $qa) {
                switch ($qa['id_pertanyaan']) {
                    case 26: // Nama Anggota Keluarga
                        $mappedData['nama'] = $qa['jawaban_essay'] ?? '';
                        break;
                    case 29: // Jenis Kelamin
                        // Asumsi 1=Laki-laki, 2=Perempuan sesuai select option di form
                        $mappedData['jenis_kelamin'] = $qa['no_jawaban_pilihan'] ?? '';
                        break;
                    case 30: // Tanggal Lahir
                        $mappedData['tgl_lahir'] = $qa['jawaban_essay'] ?? '';
                        break;
                    case 31: // Status Perkawinan
                        // Asumsi 1=Belum Kawin, 2=Kawin/Nikah, 3=Cerai Hidup, 4=Cerai Mati
                        $mappedData['status_kawin'] = $qa['no_jawaban_pilihan'] ?? '';
                        break;
                    case 36: // Agama
                        // Asumsi 1=Islam, 2=Kristen, dst.
                        $mappedData['agama'] = $qa['no_jawaban_pilihan'] ?? '';
                        break;
                }
            }
            return $mappedData;
        }
        Log::error('Helper: Struktur data individu tidak ditemukan dalam respons API. Response: ' . $response);
        return null; // Kembalikan null jika data tidak ditemukan atau struktur tidak sesuai
    }
}
