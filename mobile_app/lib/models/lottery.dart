class Lottery {
  final int id;
  final String name;
  final String? code;
  final String? drawTime;
  final int closeBeforeMinutes;
  final String salesStatus;
  final bool autoCloseEnabled;

  const Lottery({
    required this.id,
    required this.name,
    this.code,
    this.drawTime,
    this.closeBeforeMinutes = 10,
    this.salesStatus = 'open',
    this.autoCloseEnabled = true,
  });

  bool get isOpen => salesStatus == 'open';

  factory Lottery.fromJson(Map<String, dynamic> json) {
    return Lottery(
      id: int.tryParse('${json['id']}') ?? 0,
      name: (json['name'] ?? '').toString(),
      code: json['code']?.toString(),
      drawTime: json['draw_time']?.toString(),
      closeBeforeMinutes: int.tryParse('${json['close_before_minutes'] ?? 10}') ?? 10,
      salesStatus: (json['sales_status'] ?? 'open').toString(),
      autoCloseEnabled: '${json['auto_close_enabled'] ?? 1}' == '1' || json['auto_close_enabled'] == true,
    );
  }
}
