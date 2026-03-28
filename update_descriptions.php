<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$descriptions = [
    'Salmón Premium' => 'Exquisito filete de salmón importado de grado superior, rico en Omega-3. Su color vibrante y textura mantecosa lo hacen inigualable para preparaciones crudas finas como sushi o un sellado perfecto a la plancha. Sabor de mar incomparable.',
    'Cangrejo Rojo' => 'Espectacular cangrejo rojo vivo seleccionado a mano. Destaca por su generoso tamaño y pinzas carnosas, garantizando una experiencia gastronómica inmersiva. El alma indiscutible de cualquier parrillada costera o festival de mariscos familiar.',
    'Langosta Viva Premium' => 'Joya del mar entregada viva para garantizar frescura tridimensional. Carne dulce, tierna y abundante que se derrite en el paladar. Un lujo culinario reservado para banquetes y las ocasiones más exclusivas y demandantes.',
    'Filete de Corvina' => 'Majestuosos filetes limpios de corvina capturada a gran profundidad. Su carne blanca y de textura muy firme ofrece un bocado delicado. El ingrediente estrella indiscutible para un ceviche o tiradito peruano de altísima calidad.',
    'Camarones Jumbo exportación' => 'Colosales camarones desvenados y pelados (Calibre U-15 calidad de exportación). Textura crujiente y tamaño espectacular que dominan el plato, soberbios para lucirse al ajillo, al carbón o en una copa de coctel premium.',
    'Atún Rojo (Lomo)' => 'Corte maestro de lomo de atún aleta amarilla (Grado Sashimi A+). Un bloque rubí de pura proteína magna del mar, sin venas y de corte impoluto. Perfecto para tatakis sellados al sésamo, tartar fino o consumirlo 100% crudo.',
    'Anillos de Calamar' => 'Aros pulcros y tiernos de calamar fresco. Su textura suave los hace ideales para lograr un empanizado dorado y crujiente (Rabas estilo mediterráneo) o para potenciar contundentes estofados y arroces marineros.',
    'Pargo Rojo Entero' => 'Hermoso Pargo escamado y enteramente desviscerado. Su carne blanca con toques dulces es aclamada mundialmente. Perfecto para hornearlo entero o freírlo profundo para un crujiente supremo.',
    'Mejillones Media Concha' => 'Selectos mejillones importados de aguas gélidas, presentados en su espectacular media concha esmeralda. El trofeo visual indispensable para paellas de gran escala o pastas a la crema de mar.',
    'Dorado (Mahi Mahi)' => 'Gruesos lingotes de pez Dorado fresco de temporada. Reconocido por su carne magra jugosa y extrema firmeza al calor. Ideal para cortes gruesos a la parrilla, resistiendo perfectamente adobos y marinados.'
];

foreach ($descriptions as $nombre => $desc) {
    if (strpos($nombre, 'At') === 0) {
        $prod = \App\Models\Producto::where('nombre', 'like', 'At%n%Lomo%')->first();
    } else {
        $prod = \App\Models\Producto::where('nombre', $nombre)->first();
    }
    
    if ($prod) {
        $prod->descripcion = $desc;
        $prod->save();
        echo "Updated: " . $prod->nombre . "\n";
    }
}
echo "Done.\n";
