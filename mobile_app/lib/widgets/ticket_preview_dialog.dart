import 'package:flutter/material.dart';
import '../models/lottery.dart';
import '../models/ticket_play.dart';
import '../models/tirage.dart';

Future<bool> showTicketPreviewDialog({
  required BuildContext context,
  required Lottery lottery,
  Tirage? tirage,
  required List<TicketPlay> plays,
  required double total,
}) async {
  final confirmed = await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('Prévisualisation fiche'),
      content: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Lottery: ${lottery.name}', style: const TextStyle(fontWeight: FontWeight.bold)),
            if (tirage != null) Text('Tirage: ${tirage.drawName} ${tirage.drawDate ?? ''}'),
            if (lottery.drawTime != null) Text('Heure: ${lottery.drawTime}'),
            const Divider(),
            for (final play in plays)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 3),
                child: Row(
                  children: [
                    Expanded(child: Text('${play.number}  ${play.type}')),
                    Text('${play.amount.toStringAsFixed(2)} HTG'),
                  ],
                ),
              ),
            const Divider(),
            Row(
              children: [
                const Expanded(child: Text('TOTAL', style: TextStyle(fontWeight: FontWeight.bold))),
                Text('${total.toStringAsFixed(2)} HTG', style: const TextStyle(fontWeight: FontWeight.bold)),
              ],
            ),
          ],
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Annuler')),
        FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('Confirmer')),
      ],
    ),
  );
  return confirmed == true;
}
