<?php
$homeWorkNum = '2.3';
$homeWorkCaption = 'PHP и HTML.';
$testReady = false;
$fileName = 'tests.json';
$filePath = __DIR__ . '/uploadedFiles/' . $fileName;
$additionalHint = '';
$secondHint = '';
$errorCounts = 0; /* Количество ошибок */
$userScore = 0; /* Баллы, которые набрал тестируемый */
$maxScore = 0; /* Максимальное количество баллов, которое можно получить */
$errorCode = null;


/* проверяем есть ли файл и извлекаем тест из файла */
if (is_file($filePath)) {
    $tests = json_decode(file_get_contents($filePath), true);
    /* Получаем номер теста в зависимости от запроса */
    if (isset($_GET['testNum'])) {
        $testNum = $_GET['testNum'];
    } elseif (isset($_POST['testNum'])) {
        $testNum = $_POST['testNum'];
    }
    /* делаем проверку по номеру теста */
    if (isset($testNum) && isset($tests[$testNum])) {
        $test = $tests[$testNum];
        $testReady = true;
        $maxScore = count($test['main']);
        $userScore = $maxScore;
    } else {
        $testReady = false;
        if (!headers_sent()) {
            if (isset($testNum)) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                $errorCode = 404;
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                $errorCode = 400;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
  <head>
    <title>Домашнее задание по теме <?= $homeWorkNum ?> <?= $homeWorkCaption ?></title>
    <meta charset="utf-8">
    <style>
      form {
        display: inline-block;
      }

      div {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <h1>Интерфейс прохождения выбранного теста</h1>

    <form method="post" enctype="multipart/form-data">
      <p>
        <label>Ваше имя: <input type="text" placeholder="Введите здесь Ваше имя" name="UserName"
                                value="<?= (isset($_POST['UserName'])) ? $_POST['UserName'] : '' ?>">
        </label>
      </p>
      <fieldset>
        <?php if ($testReady && isset($test)) { ?>

        <legend><?= $test['testName'] ?></legend>

          <?php
          foreach ($test['main'] as $questionNum => $question):
              $questionType = ($question['type'] == 'single' ? 'radio' : 'checkbox');
              $i = 0;
              ?>

          <fieldset>
            <legend><?= $question['question'] ?></legend>

            <?php
            foreach ($question['answers'] as $answerNum => $answer):
                ++$i;
                $color = 'black';
                $fontWeight = 'normal';
                $labelName = ($question['type'] == 'single' ? $questionNum : $questionNum . '|' . $answerNum);
                /*Если label - это чекбокс, то делаем имя $labelName в таком формате: "вопрос + | + № ответа",
                иначе - только имя вопроса. Это нужно для правильной работы переключателей и передачи параметров
                для проверки теста */

                if (isset($_POST['ShowTestResults'])) {
                    /* Если нажали ShowTestResults (проверка результатов) - расставляем правильно галки и проверяем
                    результат (для правильного выбора делаем цвет текста зеленым, для неправильного (и для
                    невыбранных правильных значений) - красным и выделяем жирным) */

                    if (isset($_POST[$labelName]) && $_POST[$labelName] === $answer) {
                        $needChecked = 'Checked';
                    } else {
                        $needChecked = '';
                    }

                    if (!empty($_POST['UserName'])) {
                        if (isset($_POST[$labelName]) && $_POST[$labelName] === $answer) {
                            if (in_array($_POST[$labelName], $question['rightAnswers'])) {
                                $color = 'Green';

                            } else {
                                $color = 'Red';
                                $fontWeight = 'Bold';
                                $errorCounts++;
                                /*подсчитываем баллы - за каждый неправильный ответ -1 балл, если в вопросе
                                несколько ответов, то -(1/количество ответов) */
                                $userScore = ($questionType == 'radio') ? --$userScore : $userScore -
                                    (1 / count($question['answers']));
                            }
                        } elseif (in_array($answer, $question['rightAnswers'])) {
                            $color = 'Red';
                            $fontWeight = 'Bold';
                            if (isset($_POST[$labelName]) === false) {
                                $errorCounts++;
                                $userScore = ($questionType == 'radio') ? --$userScore : $userScore -
                                    (1 / count($question['answers']));
                            }
                        }
                    }
                } else {
                    /* Если кнопка ShowTestResults не была нажата, то для первых элементов типа radio
                    ставим атрибут Checked */
                    $needChecked = ($i === 1 && $questionType !== 'checkbox' ? 'Checked' : '');
                }
                ?>

            <label style="color: <?= $color ?>; font-weight: <?= $fontWeight ?>"><input
                type="<?= $questionType ?>" name="<?= $labelName ?>"
                value="<?= $answer ?>" <?= $needChecked ?>><?= $answer ?>
            </label>

            <?php endforeach; ?>

          </fieldset>

        <?php endforeach; ?>

        <hr>

        <?php
        /* вывод подсказки при нажатии ShowTestResults */
        if (isset($_POST['ShowTestResults'])) {
            if (empty($_POST['UserName'])) {
                $additionalHint = 'Вы не указали имя!';
            } else {
                if ($errorCounts == 0) {
                    $additionalHint = $_POST['UserName'] . ', Вы правильно ответили на все вопросы! Поздравляем!';
                } else {
                    $additionalHint = $_POST['UserName'] . ', Вы завершили тест. Количество ошибок: ' . $errorCounts . ' шт.';
                }
                session_start();
                $userScore = round($userScore, 2);
                $_SESSION['userName'] = $_POST['UserName'];
                $_SESSION['errorCounts'] = $errorCounts;
                $_SESSION['userScore'] = $userScore;
                $_SESSION['maxScore'] = $maxScore;
                $_SESSION['testName'] = $test['testName'];
                $secondHint = 'Вы набрали ' . $userScore . ' баллов из ' . $maxScore . ' возможных.';
                ?>

        <img src="certificate.php" alt="Сертификат">

        <?php
            }
        }
        ?>

        <p><?= $additionalHint ?></p>
        <p><?= $secondHint ?></p>
        <div>
          <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<<= Вернуться к загрузке файла"
                 title="Вернуться к загрузке файла">
          <input type="submit" formaction="list.php" name="ShowListForm" value="<= Вернуться к выбору теста"
                 title="Вернуться к выбору теста">
          <input type="hidden" name="testNum" value="<?= (isset($testNum) ? $testNum : 0) ?>">
          <input type="submit" formaction="test.php" name="ShowTestResults" value="Проверить"
                 title="Проверить результаты теста">
        </div>

        <?php } else { ?>

        <legend>Тесты</legend>

        <?php
        switch ($errorCode) {
            case 400:
                echo '<h2>400 Bad Request</h2>';
                $additionalHint = 'Не указан номер теста.';
                break;

            case 404:
                echo '<h2>404 Not Found</h2>';
                $additionalHint = 'Указан неправильный номер теста, или тест не найден в загруженном файле.';
                break;

            default:
                $additionalHint = 'Не удалось извлечь список тестов.';
        }
        ?>

        <p><?= $additionalHint ?></p>
        <p>Попробуйте вернуться, выбрать тест заново или загрузить новый файл с тестами.</p>
        <div>
          <input type="submit" formaction="admin.php" name="ShowAdminForm" value="<<= Вернуться к загрузке файла"
                 title="Вернуться к загрузке файла">
          <input type="submit" formaction="list.php" name="ShowListForm" value="<= Вернуться к выбору теста"
                 title="Вернуться к выбору теста">
        </div>

        <?php } ?>

      </fieldset>
    </form>
  </body>
</html>
