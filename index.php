<?php
include 'db.php';
$sql = "SELECT direction FROM robot_control WHERE id = 1";
$result = $conn->query($sql);
$current_direction = "stop";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_direction = $row['direction'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <style>
        body { font-family: Tahoma, sans-serif; text-align: center; background: #f4f4f9; margin-top: 50px; }
        .container { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 350px; }
        .btn { padding: 15px 25px; margin: 5px; font-size: 16px; cursor: pointer; border: none; border-radius: 5px; background: #3498db; color: white; }
        .btn-stop { background: #e74c3c; }
        .mic-btn { width: 80px; height: 80px; border-radius: 50%; background: #2ecc71; border: none; cursor: pointer; color: white; font-size: 24px; }
        textarea { width: 100%; height: 80px; margin-top: 10px; padding: 10px; box-sizing: border-box; }
    </style>
</head>
<body>

    <h1>لوحة التحكم التفاعلية</h1>

    <div class="container">
        <div class="card">
            <h3>المساعد الصوتي</h3>
            <button class="mic-btn" onclick="startSpeechRecognition()">🎤</button>
            <textarea id="speechText" placeholder="سيظهر النص هنا..."></textarea>
            <p id="speechStatus"></p>
        </div>

        <div class="card">
            <h3>تحكم الروبوت</h3>
            <div>
                <button class="btn" onclick="sendDirection('forward')">أمام</button>
            </div>
            <div>
                <button class="btn" onclick="sendDirection('left')">يمين</button>
                <button class="btn btn-stop" onclick="sendDirection('stop')">توقف</button>
                <button class="btn" onclick="sendDirection('right')">يسار</button>
            </div>
            <div>
                <button class="btn" onclick="sendDirection('backward')">خلف</button>
            </div>
            <p>الحالة: <span id="currentStatus"><?php echo $current_direction; ?></span></p>
        </div>
    </div>

    <script>
        function sendDirection(dir) {
            fetch('save_direction.php?dir=' + dir)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('currentStatus').innerText = dir;
                });
        }

        function startSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert("المتصفح لا يدعم التعرف على الصوت");
                return;
            }
            const recognition = new SpeechRecognition();
            recognition.lang = 'ar-SA';
            recognition.start();

            recognition.onresult = function(event) {
                const speechResult = event.results[0][0].transcript;
                document.getElementById('speechText').value = speechResult;
                
                const formData = new FormData();
                formData.append('spoken_text', speechResult);

                fetch('save_speech.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('speechStatus').innerText = "تم حفظ النص بنجاح!";
                });
            };
        }
    </script>
</body>
</html>
