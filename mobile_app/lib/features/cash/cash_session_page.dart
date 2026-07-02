import 'package:flutter/material.dart';

class CashSessionPage extends StatelessWidget {
  const CashSessionPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Cash Session')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(children: [
          const Text('Aucune session ouverte'),
          const SizedBox(height: 16),
          SizedBox(width: double.infinity, child: FilledButton(onPressed: () {}, child: const Text('Ouvrir session'))),
          SizedBox(width: double.infinity, child: OutlinedButton(onPressed: () {}, child: const Text('Fermer session'))),
        ]),
      ),
    );
  }
}
