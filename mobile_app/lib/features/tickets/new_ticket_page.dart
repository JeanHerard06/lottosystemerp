import 'package:flutter/material.dart';

class NewTicketPage extends StatelessWidget {
  const NewTicketPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouveau ticket')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(children: [
          DropdownButtonFormField(items: const [DropdownMenuItem(value: 'borlette', child: Text('Borlette'))], onChanged: (_) {}, decoration: const InputDecoration(labelText: 'Jeu')),
          const TextField(decoration: InputDecoration(labelText: 'Numéro')),
          const TextField(decoration: InputDecoration(labelText: 'Montant'), keyboardType: TextInputType.number),
          const SizedBox(height: 16),
          SizedBox(width: double.infinity, child: FilledButton(onPressed: () {}, child: const Text('Enregistrer'))),
        ]),
      ),
    );
  }
}
