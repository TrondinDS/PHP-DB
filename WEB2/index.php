<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №5</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #progress-container {
            width: 100%;
            height: 20px;
            border: 1px solid #ccc;
            margin-top: 10px;
            overflow: hidden;
        }

        #margin{
            margin: 10px,10px,10px,10px;
        }

        #progress-bar {
            height: 100%;
            background-color: #4CAF50;
            color: #fff;
            text-align: center;
            line-height: 20px;
        }
    </style>
</head>

<body>
    <div id="progress-container">
        <div id="progress-bar" style="width: 0%;"></div>
    </div>
    <button id="startBtn" class="margin">Начать загрузку</button>
    <button id="stopBtn" class="margin">Остановить загрузку</button>
    <button id="openFilterBtn1"class="margin">Открыть filter.html</button>
    <button id="openFilterBtn2"class="margin">Открыть filterR.html</button>
    <script>
        var progress = 0;
        var pause = false;
        var progressInterval;
        $(document).ready(function() {
            $('#startBtn').click(function() {
                startUpload();
            });
            $('#stopBtn').click(function() {
                stopUpload();
            });

            $('#openFilterBtn1').click(function() {
                window.open('filter.html', '_blank');
            });

            $('#openFilterBtn2').click(function() {
                window.open('filterR.html', '_blank');
            });

            function stopUpload() {
                clearInterval(progressInterval);
            }

            function startUpload() {
                progressInterval = setInterval(
                    function() {
                        $.ajax({
                            url: 'upload.php',
                            method: 'POST',
                            success: function(response) {
                                updateProgress();
                                console.log(response);
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        })
                    }, 2000);

                function updateProgress() {
                    progress++;
                    if (progress >= 100) {
                        clearInterval(progressInterval);
                    }
                    $('#progress-bar').css('width', progress + '%');
                    $('#progress-bar').text(progress + '%');
                    setProgressCookie(progress);
                }
            }

            function setProgressCookie(progress) {
                document.cookie = "progress=" + progress + "; expires=" + new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString() + "; path=/";
            }

            function getProgressCookie() {
                var cookies = document.cookie.split("; ");
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i].split("=");
                    if (cookie[0] === "progress") {
                        return parseInt(cookie[1]);
                    }
                }
                return 0; // Возвращаем 0, если кука не найдена
            }

            progress = getProgressCookie();
            $('#progress-bar').css('width', progress + '%');
            $('#progress-bar').text(progress + '%');

        });
    </script>
</body>

</html>