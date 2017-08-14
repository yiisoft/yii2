Paginacja
=========

Kiedy danych jest zbyt dużo, aby wyświetlić je w całości na jednej stronie, zwykle stosuje się mechanizm podziału na wiele stron, 
z których każda prezentuje tylko część danych na raz. Mechanizm ten nazywamy *paginacją*.

W Yii obiekt [[yii\data\Pagination|Pagination]] reprezentuje zbiór informacji o schemacie paginacji.

* [[yii\data\Pagination::$totalCount|liczba wyników]] określa całkowitą liczbę elementów zestawu danych. Zwykle jest to znacznie większa liczba 
  niż ilość elementów, które można umieścić na pojedynczej stronie.
* [[yii\data\Pagination::$pageSize|rozmiar strony]] określa jak wiele elementów może znaleźć się na pojedynczej stronie. Domyślna wartość to 20.
* [[yii\data\Pagination::$page|aktualna strona]] wskazuje numer aktualnie wyświetlanej strony (począwszy od zera). Domyślna wartość to 0, wskazująca na pierwszą stronę.

Korzystając z w pełni zdefiniowanego obiektu [[yii\data\Pagination|Pagination]], można pobrać i wyświetlić dane w partiach. Dla przykładu, przy pobieraniu danych z bazy można 
użyć wartości `OFFSET` i `LIMIT` w kwerendzie, które będą odpowiadać tym zdefiniowanym przez paginację.

```php
use yii\data\Pagination;

// stwórz kwerendę bazodanową, aby pobrać wszystkie artykuły o statusie = 1
$query = Article::find()->where(['status' => 1]);

// ustal całkowitą liczbę artykułów (ale nie pobieraj jeszcze danych artykułów)
$count = $query->count();

// stwórz obiekt paginacji z całkowitą liczbą wyników
$pagination = new Pagination(['totalCount' => $count]);

// ogranicz wyniki kwerendy korzystając z paginacji i pobierz artykuły
$articles = $query->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();
```

Która strona wyników z artykułami zostanie pobrana w powyższym przykładzie? To zależy od tego, czy ustawiono parametr kwerendy `page`. 
Domyślnie paginacja próbuje ustawić [[yii\data\Pagination::$page|aktualną stronę]] na odpowiadającą wartości parametru `page`. 
Jeśli ten parametr nie jest przekazany, wartość będzie domyślna, czyli 0.

Aby ułatwić tworzenie elementów UI, które będą odpowiedzialne za korzystanie z mechanizmu paginacji, Yii posiada wbudowany widżet [[yii\widgets\LinkPager|LinkPager]], 
który wyświetla listę przycisków z numerami, po kliknięciu których użytkownik przechodzi do pożądanej strony wyników. 
Widżet korzysta z obiektu paginacji, dzięki czemu wie, który numer ma aktualnie wyświetlana strona i jak wiele przycisków stron powinien wyświetlić. 
Przykład:

```php
use yii\widgets\LinkPager;

echo LinkPager::widget([
    'pagination' => $pagination,
]);
```

Jeśli chcesz samemu stworzyć takie elementy UI, możesz skorzystać z metody [[yii\data\Pagination::createUrl()|createUrl()]], aby uzyskać adresy URL poszczególnych stron. 
Metoda ta wymaga podania parametru page i zwraca poprawnie sformatowany adres URL zawierający ten parametr. Przykładowo,

```php
// określa trasę, która będzie zawarta w nowoutworzonym adresie URL
// Jeśli nie będzie podana, użyta zostanie aktualna trasa
$pagination->route = 'article/index';

// wyświetla: /index.php?r=article%2Findex&page=100
echo $pagination->createUrl(100);

// wyświetla: /index.php?r=article%2Findex&page=101
echo $pagination->createUrl(101);
```

> Tip: Możesz zmodyfikować nazwę parametru kwerendy `page`, poprzez ustawienie właściwości 
> [[yii\data\Pagination::pageParam|pageParam]] w czasie tworzenia obiektu paginacji.
