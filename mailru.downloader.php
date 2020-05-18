<?php

$VideoPage_URL = @$argv[1]; // -> https://my.mail.ru/mail/ik0717/video/_myvideo/267.html

If (empty($VideoPage_URL) == True) {
    Echo "\n - Mail.RU Movie Downloader. \n";
    Echo "\n > Usage: php " . $argv[0] . " https://my.mail.ru/mail/ik0717/video/_myvideo/267.html \n \n";
    Die;
}

$VideoMeta_TempFile = "";
$VideoList_SplitDelimeter = "©";
$ActiveDir = getcwd(); // -> /root/downloads
$VideoStorage_Dir = $ActiveDir."/mailru.videos/";
$VideoStorage_TempDir = $ActiveDir."/mailru.temp/";
$VideoPage_MobilURL = str_replace("//my.mail.ru/", "//m.my.mail.ru/", $VideoPage_URL);
$VideoPage_MobilURL = str_replace("/video", "/video", $VideoPage_MobilURL);
$VideoPage_MobilURL_HashFile = $VideoStorage_TempDir.crc32($VideoPage_MobilURL)."_page.html";

Echo "\n> ".$VideoPage_URL." -> ".$VideoPage_MobilURL." \n \n"; // ->  https://my.mail.ru/mail/ik0717/video/_myvideo/267.html -> https://my.mail.ru/mail/ik0717/video/_myvideo/267.html

if (file_exists($VideoPage_MobilURL_HashFile) == True) {
    $VideoPage_MobilURL_PageData = file_get_contents($VideoPage_MobilURL_HashFile);
} Else {
    $VideoPage_MobilURL_PageData = file_get_contents($VideoPage_MobilURL);
    @mkdir($VideoStorage_TempDir);
    file_put_contents($VideoPage_MobilURL_HashFile, $VideoPage_MobilURL_PageData);
}

$VideoPage_MobilURL_PageData_ParseForMetaURL = $VideoPage_MobilURL_PageData;
$VideoPage_MobilURL_PageData_ParseForMetaURL = str_replace("data-meta-url=".chr(34), $VideoList_SplitDelimeter, $VideoPage_MobilURL_PageData_ParseForMetaURL);
$VideoPage_MobilURL_PageData_ParseForMetaURL = str_replace(chr(34)."><div class=".chr(34)."eventContent__gag", $VideoList_SplitDelimeter, $VideoPage_MobilURL_PageData_ParseForMetaURL);
$VideoPage_MobilURL_PageData_ParseForMetaURL_Arr = explode($VideoList_SplitDelimeter, $VideoPage_MobilURL_PageData_ParseForMetaURL);

if (is_array($VideoPage_MobilURL_PageData_ParseForMetaURL_Arr) == True) {
    UnSet($VideoPage_MobilURL_PageData_ParseForMetaURL_Arr[0]);
    foreach ($VideoPage_MobilURL_PageData_ParseForMetaURL_Arr as $VideoPage_MetaURL) {
        If (stripos($VideoPage_MetaURL, "/video/meta/") <> False) {
            $VideoPage_MetaURL_CookieFile = $VideoStorage_TempDir.crc32($VideoPage_MetaURL)."_cookie.txt";
            shell_exec(" wget -qO- ".$VideoPage_MetaURL." --save-cookies ".$VideoPage_MetaURL_CookieFile." > /dev/null ");
            if (file_exists($VideoPage_MetaURL_CookieFile) == True) {
                Echo "> Cookie File Downloaded. -> ".$VideoPage_MetaURL_CookieFile." \n \n";

                $VideoPage_MetaHTML = file_get_contents($VideoPage_MetaURL);
                $VideoPage_MetaHTML_JSON = @json_decode($VideoPage_MetaHTML, True);
                $Video_Title = $VideoPage_MetaHTML_JSON["meta"]["title"];
                $Video_Title = Trim(preg_replace( '/[^a-z0-9]+/', '_', strtolower($Video_Title)));

                Foreach ($VideoPage_MetaHTML_JSON["videos"] as $VideoQualities) {
                    @mkdir($VideoStorage_Dir);
                    $Video_SaveFile = $VideoStorage_Dir.$Video_Title."_".$VideoQualities["key"].".mp4";
                    $Video_Nohup_File = $VideoStorage_Dir.$Video_Title."_".$VideoQualities["key"]."_nohup.txt";
                    $Video_Download_CMD =  "nohup wget -O ".$Video_SaveFile." ".chr(34)."http:".$VideoQualities["url"].chr(34)." --load-cookies ".$VideoPage_MetaURL_CookieFile." > ".$Video_Nohup_File." &";
                    Echo "> Download Started -> ".$Video_SaveFile." \n \n";
                    shell_exec($Video_Download_CMD);
                }

            } Else {
                Echo "> Cookie File Downloaded Failed! \n";
            }
        }
    }
}

Echo "\n";
?>
