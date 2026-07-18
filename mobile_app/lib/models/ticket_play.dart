class TicketPlay {
  final String number;
  final String type;
  final double amount;

  const TicketPlay({required this.number, required this.type, required this.amount});

  Map<String, dynamic> toJson() => {
        'number': number,
        'type': type,
        'amount': amount,
      };
}
