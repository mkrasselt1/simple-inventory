<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Einfache Inventur</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.colVis.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
</head>

<body>
    <div class="container-fluid mt-4">
        <h1>Inventur</h1>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div>
            <button onclick="startScanner()" class="btn btn-outline-primary" title="Barcode Suche">
                <i class="bi bi-search"></i> <i class="bi bi-upc-scan"></i> Barcode
            </button>
        </div>
        <div class="table-responsive">
            <table id="inventoryTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Artikelnummer</th>
                        <th>Produktbezeichnung</th>
                        <th>EAN</th>
                        <th>Menge</th>
                        <th>Preis</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="mt-3 text-center">
            <button onclick="openImportModal()" class="btn btn-secondary">CSV Import</button>
        </div>
        <div class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="selectedItem"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Aktueller Stand: <span id="currentAmount"></span></p>
                        <p>Preis: <span id="currentPrice"></span></p>
                        <div class="mb-3">
                            <label for="changeInput" class="form-label">Änderung:</label>
                            <input type="number" class="form-control" id="changeInput" placeholder="Änderung eingeben">
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button onclick="adjustAmount(10)" class="btn btn-success">+10</button>
                            <button onclick="adjustAmount(5)" class="btn btn-success">+5</button>
                            <button onclick="adjustAmount(1)" class="btn btn-success">+1</button>
                            <button onclick="adjustAmount(-1)" class="btn btn-danger">-1</button>
                            <button onclick="adjustAmount(-10)" class="btn btn-danger">-10</button>
                        </div>
                        <button onclick="addAmount()" class="btn btn-primary me-2">Hinzurechnen</button>
                        <button onclick="overwriteAmount()" class="btn btn-warning">Überschreiben</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">CSV Import Optionen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">CSV-Datei auswählen:</label>
                            <input type="file" class="form-control" id="csvFile" accept=".csv">
                        </div>
                        <div class="mb-3">
                            <label for="encoding" class="form-label">Encoding:</label>
                            <select class="form-select" id="encoding">
                                <option value="utf-8">UTF-8</option>
                                <option value="latin1">Latin-1 (ISO-8859-1)</option>
                                <option value="windows-1252">Windows-1252</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="delimiter" class="form-label">Trennzeichen:</label>
                            <select class="form-select" id="delimiter">
                                <option value=",">Komma (,)</option>
                                <option value=";">Semikolon (;)</option>
                                <option value="\t">Tab</option>
                                <option value="|">Pipe (|)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="hasHeader" class="form-label">Hat Header-Zeile?</label>
                            <select class="form-select" id="hasHeader">
                                <option value="true">Ja</option>
                                <option value="false">Nein</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="button" class="btn btn-primary" onclick="parseCSV()">Weiter</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapping Modal -->
        <div class="modal fade" id="mappingModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Spalten zuordnen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Gefundene Spalten: <span id="columnsList"></span></p>
                        <div class="mb-3">
                            <label for="mapArtikelnummer" class="form-label">Artikelnummer:</label>
                            <select class="form-select" id="mapArtikelnummer"></select>
                        </div>
                        <div class="mb-3">
                            <label for="mapEan" class="form-label">EAN:</label>
                            <select class="form-select" id="mapEan"></select>
                        </div>
                        <div class="mb-3">
                            <label for="mapBezeichnung" class="form-label">Produktbezeichnung:</label>
                            <select class="form-select" id="mapBezeichnung"></select>
                        </div>
                        <div class="mb-3">
                            <label for="mapMenge" class="form-label">Menge (Anfangsbestand):</label>
                            <select class="form-select" id="mapMenge"></select>
                        </div>
                        <div class="mb-3">
                            <label for="mapPreis" class="form-label">Preis:</label>
                            <select class="form-select" id="mapPreis"></select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="button" class="btn btn-primary" onclick="importMappedCSV()">Importieren</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Modal -->
        <div class="modal fade" id="resetModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Datenbank zurücksetzen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-danger">Warnung: Dies löscht alle Daten unwiderruflich!</p>
                        <p>Gib <strong>RESET</strong> ein, um zu bestätigen:</p>
                        <input type="text" class="form-control" id="resetCaptcha" placeholder="RESET">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="button" class="btn btn-danger" onclick="confirmReset()">Zurücksetzen</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="scanner" style="display: none;">
            <button id="closeScanner" onclick="closeScanner()">Schließen</button>
            <div id="interactive" class="viewport"></div>
        </div>

        <footer class="mt-5 text-center text-muted">
            <button onclick="$('#resetModal').modal('show')" class="btn btn-outline-secondary btn-sm">Datenbank zurücksetzen</button>
        </footer>

        <script>
            let inventory = [];
            let selectedItem = null;
            let table;

            $(document).ready(function() {
                table = $('#inventoryTable').DataTable({
                    responsive: true,
                    serverSide: true,
                    ajax: 'api.php',
                    columns: [{
                            data: 'artikelnummer',
                            width: '150px'
                        },
                        {
                            data: 'produktbezeichnung',
                            responsivePriority: 10
                        },
                        {
                            data: 'ean'
                        },
                        {
                            data: 'menge',
                            width: '80px'
                        },
                        {
                            data: 'preis',
                            width: '80px',
                            render: function(data) {
                                return data ? '€' + parseFloat(data).toFixed(2) : '-';
                            }
                        },
                        {
                            data: null,
                            responsivePriority: 1,
                            render: function(data, type, row) {
                                const escapedArtikelnummer = row.artikelnummer.replace(/'/g, '\\\'');
                                return '<button onclick="selectItem(\'' + escapedArtikelnummer + '\')" class="btn btn-warning btn-sm me-1">Auswählen</button>' +
                                    '<button onclick="deleteItem(\'' + escapedArtikelnummer + '\')" class="btn btn-danger btn-sm">Löschen</button>';
                            },
                            orderable: false
                        }
                    ],
                    dom: 'Bfrtip',
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    buttons: [
                        'colvis',
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: ':not(:last-child)'
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':not(:last-child)'
                            }
                        },
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':not(:last-child)'
                            }
                        }
                    ]
                });
                // Auto-refresh every 30 seconds
                setInterval(function() {
                    table.ajax.reload();
                }, 30000);
            });

            function selectItem(artikelnummer) {
                // Fetch current data for the item
                fetch('api.php')
                    .then(response => response.json())
                    .then(data => {
                        const item = data.find(i => i.artikelnummer === artikelnummer);
                        if (item) {
                            selectedItem = item;
                            document.getElementById('selectedItem').textContent = item.produktbezeichnung;
                            document.getElementById('currentAmount').textContent = item.menge;
                            document.getElementById('currentPrice').textContent = item.preis ? '€' + parseFloat(item.preis).toFixed(2) : '-';
                            document.getElementById('changeInput').value = 0;
                            $('#detailModal').modal('show');
                        }
                    });
            }

            function adjustAmount(delta) {
                const input = document.getElementById('changeInput');
                input.value = (parseInt(input.value) || 0) + delta;
            }

            function addAmount() {
                const value = parseInt(document.getElementById('changeInput').value) || 0;
                updateAmount(true, value);
            }

            function overwriteAmount() {
                const value = parseInt(document.getElementById('changeInput').value) || 0;
                updateAmount(false, value);
            }

            function updateAmount(add, value) {
                fetch('api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        artikelnummer: selectedItem.artikelnummer,
                        menge: value,
                        add: add
                    })
                }).then(() => {
                    table.ajax.reload(); // Reload all data for consistency
                });
            }

            function deleteItem(artikelnummer) {
                const escaped = artikelnummer.replace(/'/g, "\\'");
                if (confirm('Artikel ' + escaped + ' wirklich löschen?')) {
                    fetch('api.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            artikelnummer: artikelnummer
                        })
                    }).then(() => {
                        table.ajax.reload();
                        alert('Artikel gelöscht.');
                    });
                }
            }

            function startScanner() {
                fetch('api.php')
                    .then(response => response.json())
                    .then(data => {
                        inventory = data;
                        document.getElementById('scanner').style.display = 'block';
                        Quagga.init({
                            inputStream: {
                                name: "Live",
                                type: "LiveStream",
                                target: document.querySelector('#interactive'),
                                constraints: {
                                    width: 640,
                                    height: 480,
                                    facingMode: "environment"
                                }
                            },
                            locator: {
                                patchSize: "medium",
                                halfSample: true
                            },
                            numOfWorkers: 2,
                            decoder: {
                                readers: ["ean_reader"]
                            },
                            locate: true
                        }, function(err) {
                            if (err) {
                                console.log(err);
                                return;
                            }
                            Quagga.start();
                            Quagga.onDetected(function(result) {
                                const code = result.codeResult.code;
                                const item = inventory.find(i => i.ean === code || i.artikelnummer === code);
                                if (item) {
                                    selectItem(item.artikelnummer);
                                    closeScanner();
                                } else {
                                    const escapedCode = code.replace(/'/g, "\\'");
                                    alert('Artikel mit Code ' + escapedCode + ' nicht gefunden.');
                                }
                            });
                        });
                    });
            }

            function closeScanner() {
                Quagga.stop();
                document.getElementById('scanner').style.display = 'none';
            }

            function confirmReset() {
                if (document.getElementById('resetCaptcha').value === 'RESET') {
                    fetch('api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'reset'
                        })
                    }).then(() => {
                        table.ajax.reload();
                        $('#resetModal').modal('hide');
                        alert('Datenbank zurückgesetzt.');
                    });
                } else {
                    alert('Falsche Eingabe. Gib "RESET" ein.');
                }
            }

            function openImportModal() {
                $('#importModal').modal('show');
            }

            let parsedData = [];

            function parseCSV() {
                const file = document.getElementById('csvFile').files[0];
                if (!file) {
                    alert('Bitte wähle eine CSV-Datei aus.');
                    return;
                }
                const delimiter = document.getElementById('delimiter').value;
                const encoding = document.getElementById('encoding').value;
                const hasHeader = document.getElementById('hasHeader').value === 'true';
                Papa.parse(file, {
                    delimiter: delimiter,
                    encoding: encoding,
                    header: hasHeader,
                    complete: function(results) {
                        parsedData = results.data;
                        const columns = hasHeader ? results.meta.fields : results.data[0] ? Object.keys(results.data[0]).map((_, i) => `Spalte ${i+1}`) : [];
                        document.getElementById('columnsList').textContent = columns.join(', ') + ` (${results.data.length} Zeilen geparst)`;
                        // Populate selects
                        const selects = ['mapArtikelnummer', 'mapEan', 'mapBezeichnung', 'mapMenge', 'mapPreis'];
                        selects.forEach(id => {
                            const select = document.getElementById(id);
                            select.innerHTML = '<option value="">Nicht zuordnen</option>';
                            columns.forEach((col, index) => {
                                const value = hasHeader ? col : index.toString();
                                select.innerHTML += `<option value="${value}">${col}</option>`;
                            });
                        });
                        $('#importModal').modal('hide');
                        $('#mappingModal').modal('show');
                    }
                });
            }

            function importMappedCSV() {
                const artikelnummerCol = document.getElementById('mapArtikelnummer').value;
                const eanCol = document.getElementById('mapEan').value;
                const bezeichnungCol = document.getElementById('mapBezeichnung').value;
                const mengeCol = document.getElementById('mapMenge').value;
                const preisCol = document.getElementById('mapPreis').value;
                const hasHeader = document.getElementById('hasHeader').value === 'true';
                let items = parsedData.map(row => {
                    const getValue = (col) => {
                        if (!col) return '';
                        if (hasHeader) return row[col]?.trim() || '';
                        const index = parseInt(col);
                        return row[index]?.trim() || '';
                    };
                    return {
                        artikelnummer: getValue(artikelnummerCol),
                        ean: getValue(eanCol),
                        produktbezeichnung: getValue(bezeichnungCol),
                        menge: mengeCol ? parseInt(getValue(mengeCol)) || 0 : 0,
                        preis: preisCol ? parseFloat(getValue(preisCol)) || 0 : 0,
                        history: []
                    };
                });
                items = items.filter(item => item.artikelnummer && item.produktbezeichnung);
                if (items.length === 0) {
                    alert('Keine gültigen Artikel gefunden.');
                    return;
                }
                fetch('api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(items)
                    }).then(response => response.json())
                    .then(() => {
                        table.ajax.reload();
                        $('#mappingModal').modal('hide');
                        alert(`${items.length} Artikel importiert.`);
                    }).catch(err => alert('Fehler beim Import: ' + err));
            }
        </script>
    </div>
</body>

</html>