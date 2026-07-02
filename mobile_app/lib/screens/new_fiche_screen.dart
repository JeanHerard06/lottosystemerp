import 'package:flutter/material.dart';
import '../api_client.dart';

class NewFicheScreen extends StatefulWidget {
  const NewFicheScreen({super.key});

  @override
  State<NewFicheScreen> createState() => _NewFicheScreenState();
}

class _NewFicheScreenState extends State<NewFicheScreen> {
  final List<Map<String, TextEditingController>> lines = [];
  final List<String> types = [];

  @override
  void initState() {
    super.initState();
    addLine();
  }

  void addLine() {
    setState(() {
      lines.add({'number': TextEditingController(), 'amount': TextEditingController()});
      types.add('borlette');
    });
  }

  Future<void> save() async {
    final plays = <Map<String, dynamic>>[];
    for (var i = 0; i < lines.length; i++) {
      plays.add({'number': lines[i]['number']!.text.trim(), 'type': types[i], 'amount': double.tryParse(lines[i]['amount']!.text) ?? 0});
    }
    final res = await ApiClient().postJson('fiches/store.php', {'device_id': 'flutter-device', 'plays': plays});
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'OK')));
    if (res['success'] == true) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouvelle fiche')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          for (var i = 0; i < lines.length; i++)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(children: [
                  Expanded(child: TextField(controller: lines[i]['number'], decoration: const InputDecoration(labelText: 'Numéro'))),
                  const SizedBox(width: 8),
                  Expanded(child: DropdownButtonFormField(value: types[i], items: const [DropdownMenuItem(value: 'borlette', child: Text('Borlette')), DropdownMenuItem(value: 'mariage', child: Text('Mariage')), DropdownMenuItem(value: 'lotto3', child: Text('Lotto 3')), DropdownMenuItem(value: 'lotto4', child: Text('Lotto 4'))], onChanged: (v) => setState(() => types[i] = v!))),
                  const SizedBox(width: 8),
                  Expanded(child: TextField(controller: lines[i]['amount'], keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Montant'))),
                ]),
              ),
            ),
          OutlinedButton(onPressed: addLine, child: const Text('+ Ligne')),
          FilledButton(onPressed: save, child: const Text('Enregistrer')),
        ],
      ),
    );
  }
}
