import '../models/game_type.dart';
import '../models/ticket_play.dart';

class TicketValidationService {
  static String? validatePlay(TicketPlay play, int index, Map<String, GameType> games) {
    final label = 'Ligne ${index + 1}';
    if (play.number.trim().isEmpty) return '$label: nimewo a obligatwa.';
    if (play.amount <= 0) return '$label: montant lan dwe pi gran pase 0.';
    final game = games[play.type];
    if (game == null) return '$label: kalite jwèt la pa aktif.';
    final pattern = game.validationPattern;
    if (pattern != null && pattern.isNotEmpty) {
      try {
        if (!RegExp(pattern).hasMatch(play.number.trim())) {
          return '$label: ${game.inputHint}';
        }
      } on FormatException {
        return '$label: règle validation invalide; kontakte administratè a.';
      }
    }
    return null;
  }

  static String? validateTicket(List<TicketPlay> plays, List<GameType> gameTypes) {
    if (plays.isEmpty) return 'Ajoute omwen yon liy.';
    final games = {for (final game in gameTypes) game.code: game};
    final seen = <String>{};
    for (var i = 0; i < plays.length; i++) {
      final error = validatePlay(plays[i], i, games);
      if (error != null) return error;
      final game = games[plays[i].type];
      final key = '${plays[i].type}:${plays[i].number}';
      if (game?.allowDuplicate != true && seen.contains(key)) {
        return 'Nimewo ${plays[i].number} deja antre pou ${game?.name ?? plays[i].type}.';
      }
      seen.add(key);
    }
    return null;
  }
}
