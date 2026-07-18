import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../api_client.dart';
import 'claims_screen.dart';
import 'pay_gain_screen.dart';

class QrVerifyScreen extends StatefulWidget {
  const QrVerifyScreen({super.key});
  @override
  State<QrVerifyScreen> createState() => _QrVerifyScreenState();
}

class _QrVerifyScreenState extends State<QrVerifyScreen> {
  final code = TextEditingController();
  Map<String, dynamic>? result;
  bool scanning = false;
  bool loading = false;

  Future<void> verify(String value) async {
    if (value.trim().isEmpty) return;
    setState(() { loading = true; scanning = false; });
    try {
      final res = await ApiClient().postJson('tickets/verify.php', {'code': value.trim()});
      setState(() => result = res);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Vérifier ticket')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (scanning)
          SizedBox(
            height: 280,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: MobileScanner(
                onDetect: (capture) {
                  final value = capture.barcodes.isNotEmpty ? capture.barcodes.first.rawValue : null;
                  if (value != null && value.isNotEmpty) {
                    code.text = value;
                    verify(value);
                  }
                },
              ),
            ),
          )
        else
          FilledButton.icon(onPressed: () => setState(() => scanning = true), icon: const Icon(Icons.qr_code_scanner), label: const Text('Scanner QR')),
        const SizedBox(height: 16),
        TextField(controller: code, decoration: const InputDecoration(labelText: 'Code fiche / QR')),
        const SizedBox(height: 12),
        FilledButton.icon(onPressed: loading ? null : () => verify(code.text), icon: const Icon(Icons.verified), label: Text(loading ? 'Vérification...' : 'Vérifier')),
        const SizedBox(height: 16),
        if (result != null)
          Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(result!['valid'] == true ? 'Ticket valide' : 'Ticket non valide', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: result!['valid'] == true ? Colors.green : Colors.red)),
            const SizedBox(height: 8),
            Text('Message: ${result!['message'] ?? '-'}'),
            if (result!['ticket'] != null) ...[
              const Divider(),
              Text('Fiche: ${result!['ticket']['fiche_code']}'),
              Text('Statut: ${result!['ticket']['status']}'),
              Text('Total: ${result!['ticket']['total_amount']}'),
              Text('Gain: ${result!['ticket']['gain_amount']}'),
              const SizedBox(height: 12),
              FilledButton.icon(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ClaimsScreen(initialCode: code.text.trim()))),
                icon: const Icon(Icons.assignment_turned_in),
                label: const Text('Soumettre claim'),
              ),
              const SizedBox(height: 8),
              FilledButton.icon(
                onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PayGainScreen(initialCode: code.text.trim()))),
                icon: const Icon(Icons.payments),
                label: const Text('Payer gain'),
              ),
            ]
          ]))),
      ]),
    );
  }
}
