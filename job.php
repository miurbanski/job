<?php
/*
 * Chcemy porównać dwa pliki z produktami: wypisać produkty, które różnią się pomiędzy plikami lub brakuje ich w jednym z plików.
 * Dane w każdym z tych plików są posortowane po `id`. Pobieranie i dekodowanie danych jest już zrealizowane w funkcji `getLines`.
 *
 * Funkcja `getDifferingPairs`:
 * * dla każdego produktu, które występują tylko w pierwszym pliku yield'uje parę typu `[$product1, null]`;
 * * dla każdego produktu, które występują tylko w drugim pliku yield'uje parę typu `[null, $product2]`;
 * * dla każdego produktu, który występuje w obu plikach, ale ma różne dane yield'uje parę typu `[$product1, $product2]`.
 * Niestety funkcja `getDifferingPairs` nie radzi sobie z większymi plikami: zużywa sporo RAMu i jest wolna.
 * W ogóle nie korzysta z tego, że pobierane dane są już posortowane.
 *
 * Należy zoptymalizować funkcję `getDifferingPairs`, aby działała dla dowolnie dużych plików.
 */
//
//ini_set('memory_limit', '10M'); // TODO gdy będziesz gotowy, to odkomentuj mnie

function getLines(string $sourceName): \Generator
{
    $handle = fopen("https://storage.googleapis.com/zadanie-php-dane/{$sourceName}.jsonl", 'r');
    while (!feof($handle)) {
        yield json_decode(fgets($handle), true);
    }
}

function getDifferingPairs(\Iterator $cursor1, \Iterator $cursor2): \Generator
{
    $cursor1->rewind();
    $cursor2->rewind();

    $productItemArray1 = $cursor1->current();
    $productItemArray2 = $cursor2->current();

    while ($productItemArray1 !== null || $productItemArray2 !== null) {
        if ($productItemArray1 !== null && $productItemArray2 !== null) {
            if ($productItemArray1['id'] === $productItemArray2['id']) {
                if ($productItemArray1 !== $productItemArray2) {
                    yield [$productItemArray1, $productItemArray2];
                }
                $cursor1->next();
                $cursor2->next();
            } elseif ($productItemArray1['id'] < $productItemArray2['id']) {
                yield [$productItemArray1, null];
                $cursor1->next();
            } else {
                yield [null, $productItemArray2];
                $cursor2->next();
            }
        } elseif ($productItemArray1 !== null) {
            yield [$productItemArray1, null];
            $cursor1->next();
        } else {
            yield [null, $productItemArray2];
            $cursor2->next();
        }

        $productItemArray1 = $cursor1->current();
        $productItemArray2 = $cursor2->current();
    }
}

$cursor = getDifferingPairs(getLines('1'), getLines('2'));
//$cursor = getDifferingPairs(getLines('1-small'), getLines('2-small'));

foreach ($cursor as [$product1, $product2]) {
    if ($product1 && $product2) {
        echo "{$product1['id']} jest różne \n";
    } else if ($product1) {
        echo "{$product1['id']} jest tylko w pierwszym\n";
    } else {
        echo "{$product2['id']} jest tylko w drugim\n";
    }
}

