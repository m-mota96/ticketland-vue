// export async function printZPL(labelZPL) {
//     return new Promise((resolve, reject) => {
//         if (!window.BrowserPrint) return reject("BrowserPrint SDK no cargado");

//         BrowserPrint.defaultMode = "https";
//         BrowserPrint.setupUrl = "https://127.0.0.1:9101/";
//         BrowserPrint.baseUrl = "https://127.0.0.1:9101/";

//         BrowserPrint.getDefaultDevice("printer",
//             (device) => {
//                 if (!device || !device.name) {
//                     return reject("No se detectó una impresora Zebra.");
//                 }

//                 device.send(labelZPL,
//                     () => resolve("Etiqueta impresa correctamente."),
//                     (err) => reject("Error al imprimir: " + err)
//                 );
//             },
//             (err) => reject("Error al obtener impresora: " + err)
//         );
//     });
// }

export async function printZPL(labelZPL) {
    return new Promise((resolve, reject) => {

        if (!window.BrowserPrint) {
            return reject("BrowserPrint SDK no cargado");
        }

        // Espera 200ms para asegurar que BrowserPrint terminó de cargar
        setTimeout(() => {
            BrowserPrint.getDefaultDevice("printer",
                (device) => {
                    if (!device || !device.name) {
                        return reject("No se detectó una impresora Zebra.");
                    }

                    console.log("Impresora detectada:", device);

                    device.send(
                        labelZPL,
                        () => resolve("Etiqueta impresa correctamente."),
                        (err) => reject("Error al imprimir: " + err)
                    );
                },
                (err) => reject("Error al obtener impresora: " + err)
            );
        }, 200);

    });
}
