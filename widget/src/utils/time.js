const timeZoneOffset = '+0000';

export const parseTotalTime = (insertedTimestamp, remainingSeconds) => {
  const startedAt = new Date(insertedTimestamp.replace(' ', 'T') + timeZoneOffset);
  const currentTime = new Date();

  const alreadyExpired = currentTime - startedAt;
  // total time is approximate +-1 sec due to the difference from server response time
  return Math.round(alreadyExpired / 1000 + remainingSeconds);
};

export const parseTransactionStatus = (status, inserted) => {
  const time = status.match(/[^[\]]+(?=])/g);
  const message = status
    .replace(status.match(/\[(.*?)\]/g), '')
    .trim();

  const timeDigits = time[0].split(':');
  const timeInSeconds = timeDigits[0] * 60 + parseInt(timeDigits[1], 10);

  const totalTime = parseTotalTime(
    inserted,
    timeInSeconds,
  );

  return {
    totalTime,
    countdown: timeInSeconds / (totalTime / 100),
    time: time[0],
    timeInSeconds,
    message,
  };
};
