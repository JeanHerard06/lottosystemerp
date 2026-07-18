import 'package:flutter/material.dart';

class MobileTicketView extends StatelessWidget {
  final Map<String, dynamic> fiche;
  final List details;
  final bool compact;

  const MobileTicketView({
    super.key,
    required this.fiche,
    required this.details,
    this.compact = false,
  });

  String money(Object? value) {
    final n = double.tryParse('$value') ?? 0;
    return '${n.toStringAsFixed(2)} HTG';
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Center(child: Text('LOTTO ERP', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18))),
            const Divider(),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Fiche'),
              Text('${fiche['fiche_code'] ?? fiche['local_uuid'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.bold)),
            ]),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Lottery'),
              Flexible(child: Text('${fiche['lottery_name'] ?? '-'}', textAlign: TextAlign.end)),
            ]),
            if ((fiche['tirage_name'] ?? '').toString().isNotEmpty)
              Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                const Text('Tirage'),
                Flexible(child: Text('${fiche['tirage_name']}', textAlign: TextAlign.end)),
              ]),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Date'),
              Flexible(child: Text('${fiche['created_at'] ?? ''}', textAlign: TextAlign.end)),
            ]),
            const Divider(),
            for (final d in details)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 3),
                child: Row(
                  children: [
                    Expanded(child: Text('${d['play_type'] ?? d['type'] ?? ''}'.toUpperCase())),
                    Expanded(child: Text('${d['number_played'] ?? d['number'] ?? ''}', textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.bold))),
                    Expanded(child: Text(money(d['amount']), textAlign: TextAlign.end)),
                  ],
                ),
              ),
            const Divider(),
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('TOTAL', style: TextStyle(fontWeight: FontWeight.bold)),
              Text(money(fiche['total_amount']), style: const TextStyle(fontWeight: FontWeight.bold)),
            ]),
            if (!compact) ...[
              const SizedBox(height: 12),
              const Center(child: Text('Bonne chance!', style: TextStyle(fontStyle: FontStyle.italic))),
            ],
          ],
        ),
      ),
    );
  }
}
