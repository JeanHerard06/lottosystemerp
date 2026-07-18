class GameType {
  final int id;
  final String code;
  final String name;
  final String? description;
  final String? validationPattern;
  final String inputHint;
  final String matchingEngine;
  final bool allowDuplicate;

  const GameType({
    required this.id,
    required this.code,
    required this.name,
    this.description,
    this.validationPattern,
    required this.inputHint,
    required this.matchingEngine,
    required this.allowDuplicate,
  });

  factory GameType.fromJson(Map<String, dynamic> json) => GameType(
        id: int.tryParse('${json['id'] ?? 0}') ?? 0,
        code: '${json['code'] ?? ''}',
        name: '${json['name'] ?? ''}',
        description: json['description']?.toString(),
        validationPattern: json['validation_pattern']?.toString(),
        inputHint: '${json['input_hint'] ?? 'Numéro'}',
        matchingEngine: '${json['matching_engine'] ?? 'exact_first'}',
        allowDuplicate: json['allow_duplicate'] == true || '${json['allow_duplicate']}' == '1',
      );
}
