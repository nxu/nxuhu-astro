export async function onRequest({ env }) {
    const latestSession = await env.DB
        .prepare('select "session" from "records" order by ts desc limit 1')
        .first('session');

    const { solved, dnf } = await env.DB
        .prepare(`
            SELECT 
                COUNT(1) filter(WHERE "result" <> 'DNF') as "solved",
                COUNt(1) filter(WHERE "result" = 'DNF') as "dnf"
            FROM "records"
            where "session" = ?
        `)
        .bind(latestSession)
        .first();

    const rows = await env.DB.batch([
        env.DB.prepare(`select avg("time") as "average" from records where "session" = ? and "result" <> 'DNF'`).bind(latestSession),
        env.DB.prepare(`
            select "time" as "median" from records
            where "session" = ?
                and "result" <> 'DNF'
                order by "time" asc
            LIMIT 1
            OFFSET 75
        `).bind(latestSession),

        env.DB.prepare(`
            SELECT AVG((r.time - sub.a) * (r.time - sub.a)) as var 
            FROM records r,
            (
                SELECT AVG("time") a 
                FROM records t
                WHERE "session" = ?
                AND result <> 'DNF'
            ) AS sub
            WHERE "session" = ?
            AND result <> 'DNF'
        `).bind(latestSession, latestSession),

        env.DB.prepare(`
            SELECT 
                avg("time") as average,
                "session"
            FROM "records"
            WHERE "result" <> 'DNF'
            GROUP BY "session"
            ORDER BY "ts" DESC
            LIMIT 30
        `),

        env.DB.prepare(`
            SELECT 
	            COUNT(1) as solves,
                "session"
            FROM "records"
            WHERE "result" <> 'DNF'
            GROUP BY "session"
            ORDER BY "ts" DESC
            LIMIT 30
        `),
    ]);
    
    return Response.json({
        latest: {
            solved,
            dnf,
            average: rows[0].results[0].average,
            median: rows[1].results[0].median,
            sd: Math.sqrt(rows[2].results[0].var),
            times: rows[3].results.reverse(),
            solves: rows[4].results.reverse(),
        }
    });
}
