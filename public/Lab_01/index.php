<?php // Початок php

// 1. Створення базового PHP-скрипта:
echo "Hello World!"; // Выводит текст Hello World!
echo "<br>"; // Відступ (на новий рядок)

$a = "Hello World!"; // string
$b = 5; // int
$c = 5.5; //float
$is_student = true; //bool


// 2. Змінні та типи даних:
echo $a; // вивід змінної
echo "<br>";
echo $b;
echo "<br>";
echo $c;
echo "<br>";
echo $is_student;
echo "<br>";

var_dump($a); // вивід типу змінної
echo "<br>";
var_dump($b);
echo "<br>";
var_dump($c);
echo "<br>";
var_dump($is_student);
echo "<br>";


// 3. Конкатенація рядків:
$Hello = "Hello"; // 1
$World = "World"; // 2
$HelloWorld = $Hello . " " . $World; // 1 +  + 2 = 3
echo $HelloWorld; // 3
echo "<br>";


// 4. Умовні конструкції:
$number = 3;
if ($number % 2 == 0) { // умовна конструкція if-else. Якщо залишок від ділення числа на 2 = 0, то true -> число парне
    echo "Число парне";
} else echo "Число непарне"; // false -> непарне
echo "<br>";


// 5. Цикли:
$number1 = 10;
for ($i = 1; $i <= $number1; $i++) { // поки i<=10, виводимо i, а вона з кожною ітерацією більше на 1, тобто, 1,2,..,10
    echo $i;
    echo "<br>";
}
echo "<br>";

$number2 = 1;
$j = 10;
while ($j >= $number2) { // поки j>=1, виводимо j, а вона з кожною ітерацією менше на 1, тобто 10,9,..,1
    echo $j;
    echo "<br>";
    $j = $j -1; // або $j--;
}
echo "<br>";


// 6. Масиви:
$student = [ // асоціативний масив
    "first_name" => "Дмитро",
    "last_name" => "Палажевич",
    "age" => 19,
    "speciality" => "Computer Science 122",
];

foreach ($student as $key => $value) { // виводимо кожне значення нашого масиву
    echo $value;
    echo "<br>";
}
echo "<br>";

$grade = 5; // змінна
$student["grade"] = $grade; // додаємо змінну у кінець масива

foreach ($student as $key => $value) { // виводимо кожне значення нашого масиву
    echo $value;
    echo "<br>";
}


?> <!-- Кінець php / Не обов'язковий -->
