var computed = false;
var decimal = 0;

function convert(entryform, measureField, productField) {
    // Pobranie wartości z pól formularza
    const inputValue = parseFloat(entryform.input.value);  // Wartość wpisana przez użytkownika
    const unitFactor = parseFloat(measureField.value);     // Wartość wybranej jednostki (ml/l)
    const productFactor = parseFloat(productField.value);  // Wartość wybranego produktu (cena)

    // Sprawdzamy, czy użytkownik wpisał poprawne dane
    if (isNaN(inputValue) || unitFactor === "" || productFactor === "") {
        alert("Proszę wypełnić wszystkie pola poprawnymi wartościami.");
        return;
    }

    // Przeliczamy ilość w wybranej jednostce na koszt produktu
    const result = inputValue * unitFactor * productFactor;

    // Wyświetlamy wynik w polu "display"
    entryform.display.value = result.toFixed(2); // Zaokrąglenie wyniku do dwóch miejsc po przecinku
}

function addChar (input, character)
{
    if((character == '.' && decimal == "0") || character != '.')
    {
        (input.value == "" || input.value == "0") ? input.value = character : input.value += character
        convert(input.form, input.form.measure1, input.form.measure2)
        computed = true;
        if(character=='.')
        {
            decimal = 1;
        }
    }
}

function openVothcom()
{
    window.open("", "Display window", "toolbar=no, directories=no, menubar=no");
}

function clear(form)
{
    form.input.value = 0;
    form.display.value = 0;
    decimal = 0;
}

function changeBackground(hexNumber)
{
    document.body.style.backgroundColor = hexNumber;
}