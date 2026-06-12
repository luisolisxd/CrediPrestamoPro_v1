@extends('layouts.app')

@section('contenido')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>

<div class="max-w-6xl mx-auto p-4" x-data="simuladorPrestamo">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Simulador Interactivo de Cuotas</h1>

    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <form @submit.prevent="calcularSimacion">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Monto (S/)</label>
                    <input type="number" step="0.01" x-model="form.monto" class="w-full border rounded p-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tasa Interés (%)</label>
                    <input type="number" step="0.01" x-model="form.tasa_interes" class="w-full border rounded p-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">N° Cuotas</label>
                    <input type="number" x-model="form.numero_cuotas" class="w-full border rounded p-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Frecuencia</label>
                    <select x-model="form.frecuencia" class="w-full border rounded p-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="DIARIO">DIARIO</option>
                        <option value="SEMANAL">SEMANAL</option>
                        <option value="QUINCENAL">QUINCENAL</option>
                        <option value="MENSUAL">MENSUAL</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fecha Primer Pago</label>
                    <input type="date" x-model="form.fecha_inicio" class="w-full border rounded p-2 text-sm outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2 rounded text-sm transition">
                    Calcular Proyección
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" x-show="cuotas.length > 0" x-transition style="display: none;">
        <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider">Tabla de Amortización Proyectada</h2>
                <p class="text-xs text-gray-300 mt-1">Simulación informativa previa al registro formal.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="exportarExcel()" class="bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-1.5 rounded text-xs transition flex items-center gap-1 shadow">
                    📊 Excel
                </button>
                <button type="button" @click="exportarPDF()" class="bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-1.5 rounded text-xs transition flex items-center gap-1 shadow">
                    📕 PDF
                </button>
                <button type="button" @click="exportarImagen()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded text-xs transition flex items-center gap-1 shadow">
                    🖼️ Imagen (JPG)
                </button>
            </div>
        </div>

        <div id="area-impresion" class="p-6 bg-white">
            <div class="mb-4 border-b pb-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-xs text-gray-600">
                <div><strong>Monto Proyectado:</strong> S/ <span x-text="Number(form.monto).toFixed(2)"></span></div>
                <div><strong>Tasa Efectiva:</strong> <span x-text="form.tasa_interes"></span>%</div>
                <div><strong>Frecuencia:</strong> <span x-text="form.frecuencia"></span></div>
                <div><strong>Fecha Consulta:</strong> {{ date('d/m/Y H:i') }}</div>
            </div>

            <table class="w-full text-center border-collapse text-sm" id="tabla-simulacion">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 font-bold uppercase text-xs border-b">
                        <th class="p-2 border">N° Cuota</th>
                        <th class="p-2 border">Fecha Vencimiento</th>
                        <th class="p-2 border text-green-600">Capital Amortizado</th>
                        <th class="p-2 border text-blue-600">Interés Remuneratorio</th>
                        <th class="p-2 border text-gray-900 font-black">Total Cuota Fija</th>
                        <th class="p-2 border text-red-600">Saldo Deudor Restante</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-gray-800">
                    <template x-for="cuota in cuotas" :key="cuota.numero_cuota">
                        <tr class="hover:bg-gray-50/80">
                            <td class="p-2 border font-bold" x-text="cuota.numero_cuota"></td>
                            <td class="p-2 border" x-text="formatFecha(cuota.fecha_vencimiento)"></td>
                            <td class="p-2 border text-green-700" x-text="'S/ ' + Number(cuota.capital).toFixed(2)"></td>
                            <td class="p-2 border text-blue-700" x-text="'S/ ' + Number(cuota.interes).toFixed(2)"></td>
                            <td class="p-2 border font-bold text-gray-900 bg-gray-50/50" x-text="'S/ ' + Number(cuota.total).toFixed(2)"></td>
                            <td class="p-2 border text-red-600" x-text="'S/ ' + Number(cuota.saldo_restante).toFixed(2)"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-800 text-white font-bold">
                        <td class="p-2 border" colspan="2">TOTALES ESTIMADOS</td>
                        <td class="p-2 border" x-text="'S/ ' + totalCapital"></td>
                        <td class="p-2 border" x-text="'S/ ' + totalInteres"></td>
                        <td class="p-2 border text-yellow-400 font-black" x-text="'S/ ' + totalGeneral"></td>
                        <td class="p-2 border bg-gray-900">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('simuladorPrestamo', () => ({
        form: {
            monto: 1000,
            tasa_interes: 10,
            numero_cuotas: 4,
            frecuencia: 'MENSUAL',
            fecha_inicio: new Date().toISOString().split('T')[0]
        },
        cuotas: [],
        totalCapital: '0.00',
        totalInteres: '0.00',
        totalGeneral: '0.00',

        calcularSimacion() {
            fetch("{{ route('prestamos.simular') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                this.cuotas = data.cuotas;
                let cap = this.cuotas.reduce((sum, c) => sum + parseFloat(c.capital), 0);
                let int = this.cuotas.reduce((sum, c) => sum + parseFloat(c.interes), 0);
                let tot = this.cuotas.reduce((sum, c) => sum + parseFloat(c.total), 0);
                
                this.totalCapital = cap.toFixed(2);
                this.totalInteres = int.toFixed(2);
                this.totalGeneral = tot.toFixed(2);
            })
            .catch(err => alert('Ocurrió un error al simular las cuotas.'));
        },

        formatFecha(fechaStr) {
            if(!fechaStr) return '';
            const parts = fechaStr.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        },

        exportarExcel() {
            if (typeof XLSX === 'undefined') {
                alert('La librería de Excel aún se está cargando. Intente de nuevo.');
                return;
            }
            try {
                let table = document.getElementById('tabla-simulacion');
                let wb = XLSX.utils.table_to_book(table, { sheet: "Cronograma Simulado", raw: true });
                let wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
                function s2ab(s) {
                    let buf = new ArrayBuffer(s.length);
                    let view = new Uint8Array(buf);
                    for (let i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                    return buf;
                }
                
                let blob = new Blob([s2ab(wbout)], { type: "application/octet-stream" });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `Simulacion_Prestamo_${this.form.monto}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                alert('Error al generar el archivo Excel: ' + error.message);
            }
        },

        exportarPDF() {
            if (typeof html2pdf === 'undefined') {
                alert('La librería de PDF está siendo bloqueada por el navegador.');
                return;
            }
            try {
                let elemento = document.getElementById('area-impresion');
                let clon = elemento.cloneNode(true);
                clon.style.width = '794px'; 
                clon.style.padding = '20px';
                
                let opciones = {
                    margin:       [10, 10, 10, 10],
                    filename:     `Simulacion_Cronograma_${this.form.monto}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false, letterRendering: true },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                html2pdf().set(opciones).from(clon).save();
            } catch (error) {
                alert('Error al generar el archivo PDF: ' + error.message);
            }
        },

        exportarImagen() {
            if (typeof html2canvas === 'undefined') {
                alert('La librería de Imagen aún se está cargando.');
                return;
            }
            try {
                let elemento = document.getElementById('area-impresion');
                html2canvas(elemento, { 
                    scale: 2, 
                    backgroundColor: '#ffffff', 
                    useCORS: true,
                    logging: false,
                    allowTaint: true
                }).then(canvas => {
                    let link = document.createElement('a');
                    link.download = `Cronograma_Simulado_${this.form.monto}.jpg`;
                    
                    canvas.toBlob(function(blob) {
                        let url = URL.createObjectURL(blob);
                        link.href = url;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(url);
                    }, 'image/jpeg', 0.9);
                });
            } catch (error) {
                alert('Error al generar la imagen: ' + error.message);
            }
        }
    }));
});
</script>
@endsection