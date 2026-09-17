<?php

$firstName = trim($_POST["first_name"] ?? ''); // беремо дані з форми
$lastName  = trim($_POST["last_name"] ?? '');

$pattern = '/^[a-zA-Zа-яА-ЯіІїЇєЄґҐ\'\-]+$/u'; // дозволені символи (букви укр,рус і англ, також апостроф)

if (empty($firstName) || empty($lastName)) {
    echo "Помилка: заповніть усі поля форми!";
}
elseif (!preg_match($pattern, $firstName) || !preg_match($pattern, $lastName)) { // перевірка, якщо містять - помилка
    echo "Помилка: ім'я та прізвище мають містити лише літери!";
}
else {
    echo "Доброго вечора, " . htmlspecialchars($lastName) . " " . htmlspecialchars($firstName) . ", ми з України."; // вивід із об'єднанням
}
