<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <style>
        body {
            padding: 0;
            margin: 0;
            background-color: #0f1a20;
        }

        #button-container {
            display: flex;
            height: 100vh;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }

        .primary-button {
            position: relative;
            border: 2px solid #ffffff;
            border-radius: 500px;
            width: 650px;
            height: 248px;
            overflow: hidden;
            background-color: transparent;
            text-transform: uppercase;
            color: #ffffff;
            font-size: 34px;
            font-family: "Rubik", sans-serif;
            font-weight: 700;
        }

        .primary-button:hover {
            cursor: pointer;
            border: 2px solid #0197f6;
        }

        .primary-button .round {
            border-radius: 50%;
            background-color: #0197f6;
            position: absolute;
            top: 5px;
            left: 10px;
            z-index: -1;
            animation: scale-down 0.2s forwards;
        }

        .primary-button.animate .round {
            animation: scale-up 0.5s forwards;
        }

        @keyframes scale-up {
            to {
                transform: scale(600);
            }
        }

        @keyframes scale-down {
            from {
                transform: scale(600);
            }
            to {
                transform: scale(0);
            }
        }

    </style>
</head>
<body>

<div id="button-container">
    {{--    <a id="redirectLink" href="#">--}}
    {{--        <a href="<?php echo ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">--}}
    <a href="www.hasanvince.ru/?url=vless%3A%2F%2Fdb3a7df9-7e21-44e0-96fa-4bfea004dfa8%4070.34.222.214%3A443%3Ftype%3Dtcp%26security%3Dreality%26pbk%3Dk6HcumJDRg9SjVPv4OCLi1xMVilFp0aT-1_IJrv152c%26fp%3Dfirefox%26sni%3Dgoogle.com%26sid%3D50566444%26spx%3D%252F%26flow%3Dxtls-rprx-vision%23server2-4ilqe9mf">
        <button class="primary-button">
            Перейти в приложение !
            <span class="round"/>
        </button>
    </a>
</div>


<script>
  let button = document.querySelector(".primary-button");
  let item = document.querySelector(".primary-button .round");

  button.addEventListener("mouseenter", function (event) {
    this.classList += " animate";

    let buttonX = event.offsetX;
    let buttonY = event.offsetY;

    if (buttonY < 24) {
      item.style.top = 0 + "px";
    } else if (buttonY > 30) {
      item.style.top = 48 + "px";
    }

    item.style.left = buttonX + "px";
    item.style.width = "1px";
    item.style.height = "1px";
  });

  button.addEventListener("mouseleave", function () {
    this.classList.remove("animate");

    let buttonX = event.offsetX;
    let buttonY = event.offsetY;

    if (buttonY < 24) {
      item.style.top = 0 + "px";
    } else if (buttonY > 30) {
      item.style.top = 48 + "px";
    }
    item.style.left = buttonX + "px";
  });

  window.onload = function () {
    var currentUrl = window.location.href;

    document.getElementById('redirectLink').setAttribute('href', currentUrl);
  };

</script>

</body>
</html>
