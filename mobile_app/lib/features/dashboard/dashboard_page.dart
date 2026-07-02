import 'package:flutter/material.dart';
import '../tickets/new_ticket_page.dart';
import '../cash/cash_session_page.dart';

class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Dashboard Agent')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const Row(children: [
              Expanded(child: _Card(title: 'Ventes', value: '0 HTG')),
              SizedBox(width: 12),
              Expanded(child: _Card(title: 'Commission', value: '0 HTG')),
            ]),
            const SizedBox(height: 16),
            SizedBox(width: double.infinity, child: FilledButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const NewTicketPage())), child: const Text('+ Nouveau ticket'))),
            SizedBox(width: double.infinity, child: OutlinedButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CashSessionPage())), child: const Text('Cash session'))),
          ],
        ),
      ),
    );
  }
}

class _Card extends StatelessWidget {
  final String title;
  final String value;
  const _Card({required this.title, required this.value});
  @override
  Widget build(BuildContext context) {
    return Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title), Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold))])));
  }
}
