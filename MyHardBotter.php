<?php

error_reporting(-1);


require __DIR__ . '/config.php';
require __DIR__ . '/TwistOAuth.php';
require __DIR__ . '/HardBotterModel.php';

/**
 * ここに自由に実装してください。
 * 但し、 getTwistOAuth() と action() は HardBotterModel.php 内
 * で指示されている通りに必ず実装してください。
 */
class MyHardBotter extends HardBotterModel {

    /**
     * HardBotterModel.php 内で指示されている通りに必ず実装してください。
     */
    protected function getTwistOAuth() {
        // TwistOAuthオブジェクトを返します
        return new TwistOAuth(CK,CS,AT,ATS);
    }

    /**
     * HardBotterModel.php 内で指示されている通りに必ず実装してください。
     */
    protected function action() {
        $this->checkMentions();
    }

    /**
     * メンションをチェックし、反応できるものがあればリプライで反応します。
     */
    protected function checkMentions() {
        foreach ($this->getLatestMentions() as $status) {
            // マッチングを行う(先頭のものほど優先される)
            $text = $this->match($status, array(
                '/おはよう|こんにちは|こんばんは/' => '${0}！',
                '/何時/' => function ($s, $m) {
                    return date_create('now', new DateTimeZone('Asia/Tokyo'))
                           ->format('H時i分だよー');
                },
                '/占い/' => function ($s, $m) {
                    $list = array(
                        '大吉',
                        '吉', '吉',
                        '中吉', '中吉', '中吉',
                        '小吉', '小吉', '小吉',
                        '末吉', '末吉',
                        '凶',
                    );
                    return $list[array_rand($list)];
                },
                '/(update_name)\s?(.{1,20})/u' => function ($s,$m)
                {
                  $new_name = $m{2};
                  $res = $this->getTwistOAuth()->post('account/update_profile',array ( "name" => $new_name ));
                  return "Changed my name into {$res->name} by {$s->user->screen_name}";
                },
                '/(.{1,20})(\s)?\(\@.{1,15}\)/u' => function ($s,$m)
                {
                  return $m[1]."にしたりしません";
                }
            ));
            // 結果が得られればそれを反応済みリストに追加してリプライを実行する
            if ($text !== null) {
                $this->mark($status);
                $this->reply($status, $text);
            }
        }
    }

}

// 実行します
MyHardBotter::run('MyHardBotterLog.dat');
