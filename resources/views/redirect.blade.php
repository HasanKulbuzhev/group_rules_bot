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

  // Ссылка
  function buildURL(item) {
    item.href = window.location.href;
    return true;
  }
</script>

<a onclick="return buildURL(this)" href="">
</a>

<div id="button-container">
    <a href="http://www.config.vpn.com">
        <button class="primary-button">
            Перейти в приложение !
            <span class="round"/>
        </button>
    </a>
</div>

</body>
</html>
