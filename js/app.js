$(function(){

    ///////////フッターを最下部に固定
        var $ftr = $('#footer');
        if(window.innerHeight > $ftr.offset().top + $ftr.outerHeight()){
            $ftr.attr({'style':'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'});
        }
    //////////////メッセージ表示
        var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();
        if(msg.replace(/^[\s ]+|[\s ]+$/g,"").length){
            $jsShowMsg.slideToggle('slow');
            setTimeout(function(){$jsShowMsg.slideToggle('slow');},5000);
        }
    ////////////画像ライブプレヴュー
        var $dropArea = $('.area-drop');
        var $fileInput = $('.input-file');
        $dropArea.on('dragover',function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border','3px dashed #ccc');
        });
        $dropArea.on('dragLeave',function(e){
            e.stopPropagation();
            e.preventDefault();
            $(this).css('border','none');
        });
        $fileInput.on('change',function(e){
            $dropArea.css('border','none');
            var file = this.files[0], // files配列にファイルがはいっています
                $img = $(this).siblings('.prev-img'), // jQueryのsiblingsメソッドで兄弟のimgを取得
                fileReader = new FileReader();//  ファイルを読み込むFileReaderオブジェクト
                
            //読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
            fileReader.onload = function(event){
                //読み込んだデータをimgに設定
                $img.attr('src',event.target.result).show();
            };
            //画像読み込み
            fileReader.readAsDataURL(file);
        });
    ////////////テキストエリアカウント
        var $countUp = $('#js-count'),
            $countView = $('#js-count-view');
        $countUp.on('keyup',function(e){
            $countView.html($(this).val().length);
        });

    //////////////画像切替
        var $switchImgSubs = $('.js-switch-img-sub');
        var $switchImgMain = $('.js-switch-img-main');
        $switchImgSubs.on('click',function(e){
            $switchImgMain.attr('src',$(this).attr('src'));
        });
    
    ///////////////お気に入り登録・削除
        var $like,
        likeMantorId;
        $like = $('.js-click-like') || null;//nullは「変数の中身はからですよ」と明示するために使う値
        likeMantorId = $like.data('mantorid') || null;
        //数値の0はfalseと認識されてしまう。product_idが0の場合もありえるので0もtrueとするにはundefinedとnullを判定する
        if(likeMantorId !== undefined && likeMantorId !== null){
            $like.on('click',function(){
                var $this = $(this);
                $.ajax({
                    type: "POST",
                    url: "ajaxLike.php",
                    data: {mantorid : likeMantorId}
                }).done(function(data){
                    console.log('Ajax Success');
                    //クラス属性をtoggleでつけ外しする
                    $this.toggleClass('active');
                }).fail(function(msg){
                    console.log('Ajax Error')
                });
            });
        }

    });