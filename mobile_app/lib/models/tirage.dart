class Tirage {
  final int id;
  final String drawName;
  final String? drawDate;
  final String? firstNumber;
  final String? secondNumber;
  final String? thirdNumber;
  final String? createdAt;

  const Tirage({
    required this.id,
    required this.drawName,
    this.drawDate,
    this.firstNumber,
    this.secondNumber,
    this.thirdNumber,
    this.createdAt,
  });

  factory Tirage.fromJson(Map<String, dynamic> json) {
    return Tirage(
      id: int.tryParse('${json['id']}') ?? 0,
      drawName: (json['draw_name'] ?? json['name'] ?? '').toString(),
      drawDate: json['draw_date']?.toString(),
      firstNumber: json['first_number']?.toString(),
      secondNumber: json['second_number']?.toString(),
      thirdNumber: json['third_number']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}
